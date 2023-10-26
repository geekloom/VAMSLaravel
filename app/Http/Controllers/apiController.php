<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
// use Kreait\Firebase\Contract\Firestore;
// use Kreait\Firebase\Contract\Firestore;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Database;
// use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Contract\Firestore;



class apiController extends Controller
{
    public function __construct()
    {
      $base =  base_path();
      $path = '/resources/credentials/firebase_credentials.json';
      $base_path = $base.$path;
      // echo $base_path;
      // $firebase = (new Factory)
      //   ->withServiceAccount(ServiceAccount::fromJsonFile($base_path))
      //   ->create();
    $firebase = (new Factory)->withServiceAccount($base_path);

    $this->auth = $firebase->createAuth();
    // $this->firestore = $firebase->createFirestore();
    // $firestore = new FirestoreClient();
    // print_r($this->auth);
    }


    public function test(Request $req)
    {
        echo "\nDatas showing below \n";
        print_r($req->all());die;

    }

    /* login funtion v1 */

    public function authentication(Request $req)
    {

       try {
            $user = $this->auth->signInWithEmailAndPassword($req->input('email'),$req->input('password'));
            // Successful sign-in
            $data['status'] = '200';
            $data['message'] = 'User sign-in Successfully';
            $data['data'] = $user->data();
        } catch (\Kreait\Firebase\Auth\SignIn\FailedToSignIn $e) {
            if ($e->getMessage() === 'INVALID_PASSWORD') {
                 $data['status'] = '500';
                 $data['message'] = 'User sign-in password wrong ,sign-in failed';
                 $data['data'] = 'no data found';
                // Password is incorrect
                // Inform the user with a suitable error message
            } elseif ($e->getMessage() === 'EMAIL_NOT_FOUND') {
                 $data['status'] = '500';
                 $data['message'] = 'User sign-in email id not found ,sign-in failed';
                 $data['data'] = 'no data found';
            }else{
                 $data['status'] = '500';
                 $data['message'] = 'sign-in failed,Syntax error';
                 $data['data'] = 'no data found';
                // Handle other authentication errors
            }
        }
        return $data;
    }
     /* end login funtion v1 */

    /*  USER DATS funtion v1 */ 
    public function userDatas(Request $req)
    {
       
         try {
        $token = $this->auth->createCustomToken($req->post('UID'));
        $userFirebase = $this->auth->signInWithCustomToken($token);
        $usersFromMysql = DB::table('users')->where('uid',$req->post('UID'))->get()->toArray();
            $data['status'] = '200';
            $data['message'] = 'User Data fetched Successfully';
            $data['data_Firebase'] = $userFirebase->data();
            $data['data_Mysql'] = $usersFromMysql;

     } catch (Exception $e) {

                 $data['status'] = '500';
                 $data['message'] = $e->getMessage();
                 $data['data'] = 'no data found';
     }
     return $data;
    }
   /* END USER DATA funtion v1 */

  /*  CREATE USER  funtion v1 */ 
    public function createNewUser(Request $req)
    {
       try {
        $userProperties = [
                            'email' => $req->post('email'),
                            'displayName' => $req->post('name'),
                            'password' => $req->post('password'),
                        ];
     $createdUser = $this->auth->createUser($userProperties);
     $postData=array(
            'name'=>$req->post('name'),
            'type'=>'A',
            'email'=> $req->post('email'),
             'hospital_name'=>$req->post('hospital_name'),
             'uid'=>$createdUser->uid,
              );
            // $this->auth->sendPasswordResetLink($req->post('email'));
            DB::table('users')->insert($postData);
            $data['status'] = '200';
            $data['message'] = 'User Data created Successfully';
            $data['data'] = $createdUser;
    }catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {

                 $data['status'] = '500';
                 $data['message'] = $e->getMessage();
                 $data['data'] = 'no data found';
     }
     return $data;
    }

   /* END CREATE USER  funtion v1 */ 
   /* Password Reset  funtion v1 */ 
   public function resetPassword(Request $req)
    {
     try {
     $this->auth->sendPasswordResetLink($req->get('email'));
     $data ='<div style="background-color:#E5E4E2;padding-left:30px;padding-right:30px;padding-top:10px;padding-bottom:10px;height:600px;">
            <center style="background-color:blue;"><h1 style="color:white;height:75px;font-size:60px;">VAMS</h1></center>
            <center><h4>VAMS application reset Password Link successfully sent to '.$req->get('email').'</h4>
            <p>Please check your email inbox for password reset.</p>
            </center>
            </div>';
        } catch (\Kreait\Firebase\Auth\SendActionLink\FailedToSendActionLink $e) {
            $data ='<div style="background-color:#E5E4E2;padding-left:30px;padding-right:30px;padding-top:10px;padding-bottom:10px;height:600px;">
            <center style="background-color:blue;"><h1 style="color:white;height:75px;font-size:60px;">VAMS</h1></center>
            <center><h4>'.$e->getMessage().'</h4>
            <p>Please try with valid email id.</p>
            </center>
            </div>';
     }
     return $data;
    }
   /* End Password Reset  funtion v1 */ 
   /* check user type  funtion v1 */ 
   public function checkUsersType(Request $req)
    {
       $userData =  DB::table('users')->where('uid',$req->post('UID'))->first();
       // print_r($userData->type);
       return $userData->type;
    }
   /* end check user type  funtion v1 */ 
    /* create staff  funtion v1 */ 
   public function createStaff(Request $req)
    {
        // print_r($req->all());die;
          try { 
            // Creating user in firebase
            $userProperties = [
                            'email' => $req->post('email'),
                            'displayName' => $req->post('firstName').' '.$req->post('lastName'),
                            'password' => $req->post('password'),
                        ];
                $createdUser = $this->auth->createUser($userProperties);
                // creating user in user table in database
                $postData=array(
                            'name'=> $req->post('firstName').' '.$req->post('lastName'),
                            'type'=>'ST',
                            'email'=> $req->post('email'),
                             'uid'=>$createdUser->uid,
                            );
                            // $this->auth->sendPasswordResetLink($req->post('email'));
                            DB::table('users')->insert($postData);

                        // createing user data to staff table in database
                             $staffData=array(
                            'clinicId'=> $req->post('clinicId'),
                            'staffId'=>$req->post('staffId'),
                            'firstName'=> $req->post('firstName'),
                            'lastName'=> $req->post('lastName'),
                            'email'=>$req->post('email'),
                            'phoneNumber'=> $req->post('phoneNumber'),
                            'role'=> $req->post('role'),
                            'qualifications'=> $req->post('qualifications'),
                            'expertise'=> $req->post('expertise'),
                            'registrationNumber'=> $req->post('registrationNumber'),
                            'otherDetails'=> $req->post('otherDetails'),
                            'workTimings'=> $req->post('workTimings'),
                            'creatorId'=> $req->post('creatorId'),
                            'staffType'=> $req->post('staffType'),
                            );
                            DB::table('staffs')->insert($staffData);
                            // mail user datas to email using firestore
                            // $messagePart = [
                            //         'subject' => 'Confirmation mail form VAMS',
                            //         'text' => 'Including Content of VAMS User Registion Details',
                            //         'html' => '<div style="background-color:#E5E4E2;padding-left:30px;padding-right:30px;padding-top:10px;padding-bottom:10px;"><center style="background-color:blue;"><h1 style="color:white;">VAMS</h1></center><center><h3>Congratulations from VAMS</h3></center><p>Hi'.$req->post('firstName').',</p><p>Thank you for choosing <b>VAMS</b>.Your <B>VAMS</b> aaccount registration Successfully completed..!</p><p>You can access you <b>VAMS</b> account with below details.</p><span>Link: </span><a href="https://www.google.com">https://www.google.com</a><br><span>Username: '.$req->post('email').'</span><br><span>Password: '.$req->post('password').'</span><p>Also you can also change you password using beliow link.</p><a href="http://143.198.118.84/api/v1/resetPassword?email=$email">http://143.198.118.84/api/v1/resetPassword?email='.$req->post('email').'</a><br><p><b>This is an auto-generated email.Please do not reply.</b></p></div>',
                            //     ];
                            //  $mailData = [
                            //         'to' => $req->post('email'),
                            //         'message' => $messagePart,
                            //     ];
                            // $this->firestore->collection('mail')->add($mailData);
                            $data['status'] = '200';
                            $data['message'] = 'Staff Records Created Successfully';
                            $data['data'] = $staffData;

          }catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
                 $data['status'] = '500';
                 $data['message'] = $e->getMessage();
                 $data['data'] = 'no data found';
     }
     return $data;
    }
    /* end create staff  funtion v1 */ 

}
