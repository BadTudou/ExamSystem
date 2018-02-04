<?php

namespace App\Http\Controllers\API\Papers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PaperSection\Index as IndexPaperSection;
use App\Http\Requests\PaperSection\Show as ShowPaperSection;
use App\Http\Requests\PaperSection\Store as StorePaperSection;
use App\Http\Requests\PaperSection\Update as UpdatePaperSection;
use App\Http\Requests\PaperSection\Destroy as DestroyPaperSection;
use App\Http\Resources\PaperSectionResource;
use App\Http\Resources\PaperSectionCollection;
use Validator;
use App\Paper;
use App\PaperSection;


class PaperSectionController extends Controller
{
	public function index(IndexPaperSection $request)
	{
		$paper = Paper::find($request->paper);
		$sections = explode(",", $paper->sections);
		return new PaperSectionCollection(PaperSection::whereIn('id', $sections)->paginate());
	}

	public function store(ShowPaperSection $request)
	{
		$paper = Paper::find($request->paper);
		$section = PaperSection::create($request->all());
		$sections = explode(",", $paper->sections);
		array_push($sections, $section->id);
		$paper->update(['sections' => implode(",", $sections)]);
        return new PaperSectionResource($section);
	}

	public function show(ShowPaperSection $request)
	{
		$paper = Paper::findOrFail($request->paper);
		$sections = explode(",", $paper->sections);
		if ( in_array($request->section, $sections)) {
			$section = PaperSection::findOrFail($request->section);
			return new PaperSectionResource($section);
		} else {
			return response()->json(['error'=> "No query results for model [App\PaperSection] {$request->section}"], 404);
		}
	}

	public function update(UpdatePaperSection $request)
	{
		$paper = Paper::findOrFail($request->paper);
		$sections = explode(",", $paper->sections);
		if ( in_array($request->section, $sections)) {
			$section = PaperSection::findOrFail($request->section);
			$section->update($request->all());
			return new PaperSectionResource($section);
		} else {
			return response()->json(['error'=> "No query results for model [App\PaperSection] {$request->section}"], 404);
		}
	}

	public function destroy(DestroyPaperSection $request)
	{
		$paper = Paper::findOrFail($request->paper);
		$sections = explode(",", $paper->sections);
		if ($key = array_search($request->section, $sections)) {
			PaperSection::findOrFail($request->section)->delete();
			unset($sections[$key]);
		} else {
			return response()->json(['error'=> "No query results for model [App\PaperSection] {$request->section}"], 404);
		}
		$paper->update(['sections' => implode(",", $sections)]);
	}
}