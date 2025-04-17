<?php

namespace App\Http\Controllers\API;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    public function index()
    {
        $tags = Tag::all();
        return $this->sendResponse($tags, 'Tags retrieved successfully.');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $tags = Tag::where('name', 'like', "%{$query}%")->get();
        return $this->sendResponse($tags, 'Tags retrieved successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name'
        ]);

        try {
            $tag = Tag::create([
                'name' => $request->name
            ]);

            return $this->sendResponse($tag, 'Tag created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to create tag: ' . $e->getMessage(), [], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $id
        ]);

        try {
            $tag = Tag::findOrFail($id);
            $tag->name = $request->name;
            $tag->save();

            return $this->sendResponse($tag, 'Tag updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update tag: ' . $e->getMessage(), [], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tag = Tag::findOrFail($id);
            $tag->delete();

            return $this->sendResponse([], 'Tag deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to delete tag: ' . $e->getMessage(), [], 500);
        }
    }
}
