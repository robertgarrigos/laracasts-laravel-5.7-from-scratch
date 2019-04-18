<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\Mail\ProjectCreated;

class ProjectsController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth');
      // $this->middleware('auth')->only(['store', 'update']);
      // $this->middleware('auth')->escept(['show']);
    }
    public function index()
    {
      $projects = Project::where('owner_id', auth()->id())->get();
      return view('projects.index', ['projects' => $projects]);
    }

    public function create()
    {
      return view('projects.create');
    }

    public function show(Project $project)
    {
      // Different approaches to authorization.
      // Without policies:
      // abort_unless(auth()->user()->owns($project), 403); //would need to create the owns() method
      // abort_if(! auth()->user()->owns($project), 403);
      // abort_if($project->owner_id !== auth()->id(), 403);
      // With policies:
      $this->authorize('update', $project);
      // abort_if(\Gate::denies('update', $project), 403);
      // abort_unless(\Gate::allows('update', $project), 403);
      // abort_unless(auth()->user()->can('update', $project), 403);
      // abort_if(auth()->user()->cannot('update', $project), 403);

      return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
      return view('projects.edit', compact('project'));
    }

    public function update(Project $project)
    {
      $this->authorize('update', $project);

      $project->update(request(['title', 'description']));

      return redirect('/projects');

    }

    public function destroy(Project $project)
    {
      $this->authorize('update', $project);

      $project->delete();
      return redirect('/projects');
    }

    public function store()
    {
      $attributes = request()->validate([
        'title' => ['required', 'min:3'], //validation rules can be members of an array
        'description' => 'required|min:3' //or simply separated by |
      ]);

      $attributes['owner_id'] = auth()->id();

      $project = Project::create($attributes);

      \Mail::to('robert@garrigos.cat')->send(
        new ProjectCreated($project)
      );

      return redirect('/projects');

    }
}
