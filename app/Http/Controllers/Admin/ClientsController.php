<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
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
            // جلب العملاء الذين أضافهم هذا المسوق فقط
            $clients = Client::where('user_id', $user->id)->get();
        } else {
            // جلب جميع العملاء للأدمن أو غير المسوقين
            $clients = Client::all();
        }

        return view('admin.clients.index', compact('clients'));
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
