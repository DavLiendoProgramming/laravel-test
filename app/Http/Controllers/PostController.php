<?php
namespace App\Http\Controllers;

use JWTAuth;
use App\Models\Post;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class PostController extends Controller
{
    //State Variable
    protected $user;
 
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllPosts()
    {
        $posts = DB::table('posts')->get();
        return response()->json(['posts' => $posts])->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate data
        $data = $request->only('title', 'body', 'published_at');
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'body' => 'required',
            'published_at' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        try {
            $user = JWTAuth::authenticate($request->token);
            $slug = Str::slug($request->title, '-');
            $publish_at = new Carbon($request->published_at);
            //Request is valid, create new post
            $post = $this->user->posts()->create([
                'slug' => $slug,
                'title' => $request->title,
                'body' => $request->body,
                'published_at' => $publish_at,
                'creator_id' => auth()->user()->id,
            ]);
            //Post created, return success response
            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post
            ], Response::HTTP_OK);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, bad credentials'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function getUserPostBySlug(Request $request, $slug)
    {
        $post = DB::table('posts')->where('slug', '=', $slug)->get()->paginate(10);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, post not found.'
            ], 400);
        }
        return response()->json([
            'success' => true,
            'message' => 'Got Post',
            'data' => $post
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function getUserPosts(Request $request)
    {
        try {
            $user = JWTAuth::authenticate($request->token);
            //Request is valid, create new post
            $posts = DB::table('posts')->where('creator_id', '=', $user->id)->get()->paginate(10);
            //Post created, return success response
            return response()->json([
                'success' => true,
                'message' => 'Your posts are here',
                'data' => $posts
            ], Response::HTTP_OK);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, bad credentials'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Validate data
        $data = $request->only('title', 'body', 'published_at');
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'body' => 'required',
            'published_at' => 'required',
            ]);
            
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        try {
            $slug = Str::slug($request->title, '-');
            $user = JWTAuth::authenticate($request->token);
            $published_at = new Carbon($request->published_at);
            //Request is valid, update post
            $post = DB::table('posts')
            ->where('id', '=', $id)
            ->update([
                'title' => $request->title,
                'body' => $request->body,
                'published_at' => $published_at,
                'slug' => $slug
            ]);
            //post updated, return success response
            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => $post
            ], Response::HTTP_OK);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, bad credentials'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = JWTAuth::authenticate($request->token);
            //Request is valid, create new post
            $post = DB::table('posts')->where('id', '=', $id)->delete();
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'No post found',
                    'data' => $post
                ], Response::HTTP_NOT_FOUND);
            }
            //Post deleted, return success response
            return response()->json([
                'success' => true,
                'message' => 'Your postshas been deleted',
                'data' => $post
            ], Response::HTTP_OK);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, bad credentials'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            'success' => true,
            'message' => 'post deleted successfully'
        ], Response::HTTP_OK);
    }
}