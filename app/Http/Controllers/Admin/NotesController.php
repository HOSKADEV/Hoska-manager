<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoteRequest;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = Note::latest()->get();

        return view('admin.notes.index', compact('notes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $note = new Note();
        $users = User::all();

        return view('admin.notes.create', compact('note', 'users',));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NoteRequest $request)
    {
        $data = $request->validated();
        $data['notable_id'] = Auth::user()->id; // Assuming the notable ID is the user ID
        $data['notable_type'] = User::class; // Assuming the notable type is User
        $data['user_id'] = Auth::user()->id; // استخدام معرف المستخدم الحالي بدلاً من معرف المستخدم من الطلب
        Note::create($data, );

        flash()->success('Note created successfully');
        return redirect()->route('admin.notes.index');
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
    public function edit(Note $note)
    {
        $users = User::all();
        return view('admin.notes.edit', compact('note', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NoteRequest $request, Note $note)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::user()->id; // استخدام معرف المستخدم الحالي بدلاً من معرف المستخدم من الطلب
        $note->update($data);

        flash()->success('Note updated successfully');
        return redirect()->route('admin.notes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        $note->delete();

        flash()->success('Note deleted successfully');
        return redirect()->route('admin.notes.index');
    }
}
