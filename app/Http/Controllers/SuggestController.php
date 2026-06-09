<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuggestController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Suggest/Index', [
            'query' => $request->query(),
        ]);
    }
    public function listCommentApp($id, Request $request): Response
    {
        return Inertia::render('Suggest/CommentApp', [
            'query' => $request->query(),
        ]);
    }
}
