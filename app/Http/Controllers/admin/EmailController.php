<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmailTemplate;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Image;

class EmailController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function template()
    {
        $templates = EmailTemplate::select('*')->where('email_templates.state','0')->get();
        $newcontact = DB::table('contact')->where('contact.state', '=', '0')->count();
        return view('admin.email_template')->with(array('templates'=>$templates,'newcontact'=>$newcontact));
    }
    public function draft()
    {
        $templates = EmailTemplate::select('*')->where('email_templates.state','1')->get();
        $newcontact = DB::table('contact')->where('contact.state', '=', '0')->count();
        return view('admin.email_draft')->with(array('templates'=>$templates,'newcontact'=>$newcontact
        ));
    }
    public function deletebox()
    {
        $templates = EmailTemplate::select('*')->where('email_templates.state','2')->get();
        $newcontact = DB::table('contact')->where('contact.state', '=', '0')->count();
        return view('admin.email_deletebox')->with(array('templates'=>$templates,'newcontact'=>$newcontact
        ));
    }
    public function update(Request $request, $id)
    {

        EmailTemplate::where('id', $id)->update($request->except('_token'));

        return back()->with('message', 'Email template updated successfull!');
    }
    public function destroy($id) {

        $email = EmailTemplate::findOrFail($id);
        if($email['state'] == "0"){
            $email['state'] = 1 ;
            $email->update();
            return back()->with('message', 'Email template state changed successfull!');
        }elseif($email['state'] == "1"){
            $email['state'] = 2 ;
            $email->update();
            return back()->with('message', 'Email template state changed successfull!');
        }else{
            $email->delete();
            return back()->with('message', 'Email template deleted successfull!');
        }
    }

    public function save(Request $request)
    {

        $email = new EmailTemplate($request->all()) ;

        if($file = $request->hasFile('image')) {
            $file = $request->file('image') ;
            $fileName = time().'-'.$file->getClientOriginalName();
            $destinationPath = public_path().'/images' ;
            $file->move($destinationPath,$fileName);
            $email->image = $fileName;

            Image::make($destinationPath.'/'.$fileName)->resize(300, 400)->save($destinationPath.'/tumbnail/300x400/'.$fileName);
            Image::make($destinationPath.'/'.$fileName)->resize(724, 980)->save($destinationPath.'/tumbnail/724x980/'.$fileName);
            Image::make($destinationPath.'/'.$fileName)->resize(100, 150)->save($destinationPath.'/tumbnail/100x150/'.$fileName);

        }
        $email->save() ;
        if($email['state'] == 0){
            return redirect('admin/email/template');
        }elseif ($email['state'] == 1){
            return redirect('admin/email/draft');
        }elseif ($email['state'] == 1){
            return redirect('admin/email/deletebox');
        }
    }

}
