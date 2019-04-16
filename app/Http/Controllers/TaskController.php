<?php

namespace App\Http\Controllers;

use App\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the active tasks.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Task::where('archive', 0)->orderBy('id', 'desc')->get();
    }

    /**
     * Display a listing of the archived tasks.
     * @return \Illuminate\Http\Response
     */

    public function archived()
    {
        return Task::where('archive', 1)->orderBy('id', 'desc')->get();
    }


    /**
     * Store a newly created tasks in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'body' => 'required|max:500'
        ]);

        return Task::create(['body' => request('body')]);
    }

    /**
     * Display the specified task.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Task::where('id', $id)->first();
    }

    /**
     * Update the specified task.
     *
     * @param Request $request
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'body' => 'required|max:500'
        ]);

        $task = Task::findOrFail($request->id);
        $task->body = $request->body;
        $task->due_date = $request->due_date;
        $task->save();
    }

    /**
     * Archive a specified task.
     * 
     * @param int $id
     * @return void
     */

    public function archive($id)
    {
        $task = Task::findOrFail($id);
        $task->archive = ! $task->archive;
        $task->save();
    }

    /**
     * Remove the specified task from storage.
     *
     * @param  int $id
     * @return void
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
    }
}
