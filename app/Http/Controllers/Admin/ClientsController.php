<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->is_marketer) {
            // جلب العملاء المرتبطين بالمسوق فقط
            $clients = Client::where('user_id', $user->id)->latest()->get();

            // جلب المشاريع المرتبطة بالمسوق
            $projects = Project::where('marketer_id', $user->id)->get();
            $usdRate = Setting::get('usd_rate', 140); // قيمة افتراضية
            // $eurRate = Setting::get('eur_rate', 150); // قيمة افتراضية

            // حساب مجموع العمولة الكلي بالدولار (تحويل كل عملة لـ USD)
            $exchangeRates = [
                'USD' => 1,
                'EUR' => 0.9,
                'DZD' => $usdRate,
            ];

            $totalCommissionUSD = 0;

            foreach ($projects as $project) {
                $percent = $project->marketer_commission_percent ?? 0;
                $commission = ($project->total_amount * $percent) / 100;

                if ($project->currency === 'EUR') {
                    $commission /= $exchangeRates['EUR']; // تحويل لـ USD
                } elseif ($project->currency === 'DZD') {
                    $commission /= $exchangeRates['DZD']; // تحويل لـ USD
                }

                $totalCommissionUSD += $commission;
            }

            $totalCommissionEUR = $totalCommissionUSD * $exchangeRates['EUR'];
            $totalCommissionDZD = $totalCommissionUSD * $exchangeRates['DZD'];

            // حساب العمولة لكل عميل حسب عملة مشروعاته (بدون تحويل)
            foreach ($clients as $client) {
                $clientProjects = $client->projects()->where('marketer_id', $user->id)->get();

                $clientCommissionPercent = 0;
                $clientCommissionValue = 0;
                $clientCurrency = null;

                if ($clientProjects->count()) {
                    $clientCommissionPercent = $clientProjects->avg('marketer_commission_percent');
                    $clientCurrency = $clientProjects->first()->currency;

                    foreach ($clientProjects as $project) {
                        $percent = $project->marketer_commission_percent ?? '_';
                        $commission = ($project->total_amount * $percent) / 100;
                        $clientCommissionValue += $commission;
                    }
                }

                $client->commissionPercent = $clientCommissionPercent ?? '_';
                $client->commissionValue = $clientCommissionValue ?? '_';
                $client->currency = $clientCurrency;
            }
        } else {
            // للأدمن أو غير المسوقين: جميع العملاء بدون عمولات
            $clients = Client::latest()->get();
            $totalCommissionUSD = $totalCommissionEUR = $totalCommissionDZD = 0;

            foreach ($clients as $client) {
                $client->commissionPercent = 0;
                $client->commissionValue = 0;
                $client->currency = null;
            }
        }

        return view('admin.clients.index', compact(
            'clients',
            'totalCommissionUSD',
            'totalCommissionEUR',
            'totalCommissionDZD'
        ));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $client = new Client();
        $users = User::all();

        return view('admin.clients.create', compact('client', 'users',));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::user()->id; // استخدام معرف المستخدم الحالي بدلاً من معرف المستخدم من الطلب
        $client = Client::create($data);


        // إضافة بيانات الاتصال مرتبطة بالعميل
        $client->contacts()->create([
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        flash()->success('Client created successfully');
        return redirect()->route('admin.clients.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        $users = User::all();
        return view('admin.clients.edit', compact('client', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientRequest $request, Client $client)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::user()->id; // استخدام معرف المستخدم الحالي بدلاً من معرف المستخدم من الطلب
        $client->update($data);

        // تحديث جهة الاتصال إذا وجدت أو إنشاء جديدة
        $client->contacts()->updateOrCreate(
            [], // شرط البحث (مثلاً فارغ لأن نعتبر جهة اتصال واحدة لكل عميل)
            [
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
            ]
        );

        flash()->success('Client updated successfully');
        return redirect()->route('admin.clients.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        flash()->success('Client deleted successfully');
        return redirect()->route('admin.clients.index');
    }

    public function hasProjects(Client $client)
    {
        return response()->json([
            'hasProjects' => $client->projects()->exists(),
        ]);
    }
}
