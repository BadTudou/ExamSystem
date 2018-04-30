<?php

namespace App\Http\Controllers\API;

use App\Models\Preview;
use App\Models\Video;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PreviewController extends Controller
{

    public function insert(Request $request) {


        $user  = Auth::user();

        if (!($user->hasRole('teacher') || $user->hasRole("admin"))) {
            return response()->json(['message' => 'unauthorized '],401);
        }


        $previewName = $request->input('previewName');
        $cid = $request->input('cid');
        $userid = $request->input('userid');
        $desc = $request->input('desc');
        $content = $request->input('content');
        $endTime = $request->input('endTime');



        $preview = new Preview();

        $preview->userid = $userid;
        $preview->cid = $cid;
        $preview->name = $previewName;
        $preview->desc = $desc;
        $preview->content = $content;
        $preview->end_time = $endTime;
        if($preview->save()){
            return json_encode(["status"=>0,"message"=>"更新成功！"]);
        }

    }


    public function publish(Request $request) {


        $user  = Auth::user();

        if (!($user->hasRole('teacher') || $user->hasRole("admin"))) {
            return response()->json(['message' => 'unauthorized '],401);
        }


        $oid = $request->input('oid');
        $id = $request->input('id');
        $preview = Preview::find($id);
        $preview->oid = $oid;
        $preview->is_publish = 1;
        if ($preview->save()){
            return json_encode(["status"=>0,"message"=>"发布成功！"]);
        }

    }

    public function delete(Request $request){

        $user  = Auth::user();

        if (!($user->hasRole('teacher') || $user->hasRole("admin"))) {
            return response()->json(['message' => 'unauthorized '],401);
        }


        $id = $request->input('id');
        $preview = Preview::find($id);
        if ($preview->delete()){
            return json_encode(["status"=>0,"message"=>"删除成功！"]);
        }
    }

    public function update(Request $request){

        $user  = Auth::user();

        if (!($user->hasRole('teacher') || $user->hasRole("admin"))) {
            return response()->json(['message' => 'unauthorized '],401);
        }


        $previewName = $request->input('previewName');
        $cid = $request->input('cid');
        $desc = $request->input('desc');
        $content = $request->input('content');
        $endTime = $request->input('endTime');
        $isPublish = $request->input('isPublish');
        $oid = $request->input('oid');
        $id = $request->input('id');
        $preview = Preview::find($id);
        $preview->oid = $oid;
        $preview->is_publish = $isPublish;
        $preview->name = $previewName;
        $preview->content = $content;
        $preview->end_time = $endTime;
        $preview->cid = $cid;
        $preview->desc = $desc;
        if ($preview->save()){
            return json_encode(["status"=>0,"message"=>"更新成功！"]);
        }
    }


    public function selectForCid(Request $request) {

        $user  = Auth::user();

        if (!($user->hasRole('teacher') || $user->hasRole("admin"))) {
            return response()->json(['message' => 'unauthorized '],401);
        }


        $cid = $request->input('cid');
        $doc = new Preview();
        $data = $doc->where("cid",$cid)->get()->toJson();
        return $data;
    }

    public function selectForUserid (Request $request){

        $user  = Auth::user();

        if (!($user->hasRole('teacher') || $user->hasRole("admin"))) {
            return response()->json(['message' => 'unauthorized '],401);
        }


        $userid = $request->input('userid');
        $preview = new Preview();
        $data = $preview->where("userid",$userid)->get()->toJson();
        return $data;
    }

    public function selectAll() {

        $user  = Auth::user();

        if (!$user->hasRole("admin")) {
            return response()->json(['message' => 'unauthorized '],401);
        }

        $preview = new Preview();
        $data = $preview->get()->toJson();
        return $data;
    }



}