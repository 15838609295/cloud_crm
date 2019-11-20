<?php
//
//namespace App\Http\Controllers\Admin;
//
//use Illuminate\Http\Request;
//use App\Http\Controllers\Controller;
//use App\Models\Admin\Customer;
//use App\Models\Admin\CustomerInfo;
//use App\Models\Admin\Configs;
//use App\Models\Admin\Communicationlog;
//use App\Models\Admin\Members;
//use App\Models\Admin\Project;
//use App\Models\Admin\MemberInfo;
//use App\Models\Admin\MemberSource;
//use App\Models\Admin\MemberLevel;
//use App\Models\Admin\Adminuser;
//use App\Models\Admin\AssignLog;
//use Illuminate\Support\Facades\Cache;
//use Carbon\Carbon;
//use Excel;
//
//class PBCustomerController extends Controller{
//
//    public $result = array("status"=>0,'msg'=>'请求成功','data'=>"");
//
//
//    public function index(Request $request)
//    {
////        if($request->ajax()){
////            var_dump($request->post());
////            $data = array(
////            );
////        }
//        $data['sources'] = MemberSource::get();
//        if(trim(Auth::guard('admin')->user()->power)!=''){
//            $arr = explode(',',Auth::guard('admin')->user()->power);
//            $data["adminuser"] = Adminuser::whereIn('branch_id',$arr)->where('status','=',0)->get();
//        }else{
//            $data["adminuser"] = Adminuser::where('id','=',Auth::guard('admin')->user()->id)->where('status','=',0)->get();
//        }
//        return view('admin.public_customer.index',$data);
//    }
//
//    public function exchange(Request $request)
//    {
//
//    }
//
//    //异步更新数据
//    public function ajax(Request $request, $id)
//    {
//        //批量指派
//    	if($request->c=='pl_assign'){
//    		$arr= explode(',',$request->arr);
//    		array_shift($arr);  //去掉第一个元素
//
//    		$data['recommend'] = $request->assignor;
//    		$bool=Customer::whereIn("id",$arr)->update($data);
//    		$res = Adminuser::find((int)$request->assignor);
//
//            if($bool){
//            	//数据记录
//            	foreach($arr as $val){
//            		$cust = Customer::where("id","=",(int)$val)->first()->toArray();
//            		if($cust["realname"]==''){
//                  		$cust["realname"] = '空';
//                    }
//
//                  	$this->testmail($res->name,$res->email,$cust);
//
//            		$content = $res->name.'!       '.Auth::guard('admin')->user()->name.' 指派给你一个新的客户   '.$cust["realname"].'，电话：'.$cust["mobile"].'，备注'.$cust["remarks"];
//
//            		if(!$this->ese_wechat($res->wechat_id,$content)){
//            			$this->request['msg'] = "企业信息有误！";
//			            $this->request['status'] = 1;
//			            return response()->json($this->result);
//            		}
//
//            		$boo = $this->assign_log($val,$res->name);
//            		if(!$boo){
//            			$this->request['msg'] = "插入数据失败！！！";
//			            $this->request['status'] = 1;
//			            return response()->json($this->result);
//            		}
//            	}
//            	return response()->json($this->result);
//            }else{
//            	$this->result['status']=1;
//            	return response()->json($this->result);
//            }
//        }
//
//        //批量导入客户
//        if($request->c=='batch'){
//
//        	$inputFileName = $request->file('file')->store('temporary');
//			$filePath = 'storage/app/'.iconv('UTF-8', 'GBK',$inputFileName);
//			$res123 = [];
//
//		    Excel::load($filePath, function($reader) use(&$res123) {
//		        $reader = $reader->getSheet(0);
//	        	$res123 = $reader->toArray();
//		    });
//
//		    //删除标题
//		    array_shift($res123);
//
//		    $data = [];
//		    $data_info = [];
//		    $arr_tx = [];
//		    $i = 0;
//			$j = 0;
//		    foreach($res123 as $val){
//		    	if($val[3] != ''){
//		    		if(trim($val[3])!=''){
//                        $mobile = Customer::where('mobile','=',$val[3])->first();
//                        if(!$mobile){
//                      		$data[$i]['mobile']=$val[3];
//                        }else{
//                        	$arr_tx[] = $val[3];
//                      		continue;
//                        }
//                    }
//
//			    	$data[$i]['name']=$val[8];
//			    	$data[$i]['company']=$val[0];
//			    	$data[$i]['remarks']=$val[4];
//			    	$data[$i]['project']=$val[5];
//			    	$data[$i]['created_at']=Carbon::now();
//			    	$data[$i]['updated_at']=Carbon::now();
//			    	if($val[6]=="个人"){
//			    		$data[$i]['type']=0;
//			    	}else{
//			    		$data[$i]['type']=1;
//			    	}
//
//			    	if(trim($val[7])!=''){
//			    		$source = MemberSource::where('source_name','=',trim($val[7]))->first();
//			    		if($source){
//			    			$data[$i]['source']=$source->source_name;
//			    		}else{
//			    			$data[$i]['source']='其他';
//			    		}
//			    	}else{
//			    		$data[$i]['source']='其他';
//			    	}
//
//
//			    	$data[$i]['realname']=$val[1];
//
//			    	$data[$i]['email']=$val[3].'@qq.com';
//			    	$data[$i]['created_at']=Carbon::now();
//			    	$data[$i]['updated_at']=Carbon::now();
//			    	$data[$i]['position']=$val[2];
//			    	$i++;
//		    	}
//		    }
//
//		    //开启事务
//		    DB::beginTransaction();
//	        try {
//				foreach($data as $v1){
//					$mid=Customer::insertGetId($v1);
//					$data_info[$j]['member_id']=$mid;
//					$this->assign_log($mid,Auth::guard('admin')->user()->name);
//					CustomerInfo::insertGetId($data_info[$j]);
//					$j++;
//				}
//				DB::commit();
//			} catch(\Illuminate\Database\QueryException $ex) {
//	            DB::rollback(); //回滚事务
//	            $this->request['msg'] = "更新失败！";
//	            $this->request['status'] = 1;
//	            return response()->json($this->result);
//	        }
//			if(count($arr_tx)>0){
//				$this->result['msg'] = implode(',',$arr_tx).'未导入成功！其他的导入成功！！！';
//			}else{
//				$this->result['msg'] = '导入成功！！！';
//			}
//			return response()->json($this->result);
//        }
//
//        //查询沟通记录
//        if($request->c=='selectlog'){
//        	$id = $request->id;
//            $this->result["data"] = Communicationlog::from('communicationlog as cl')
//            	->select('cl.*','au.name as adminname','c.*')
//                ->leftJoin('admin_users as au', 'au.id', '=', 'cl.admin_user_id')
//                ->leftJoin('members as c', 'c.id', '=', 'cl.member_id')
//                ->where('cl.member_id', '=', $id)
//                ->orderBy('cl.id',"desc")
//                ->get();
//
//            if(count($this->result["data"])<1){
//         		$customer = Customer::where('id','=',$id)->first();
//         		$this->result['name']=$customer->name;
//         		$this->result['realname']=$customer->realname;
//         		$this->result['mobile']=$customer->mobile;
//         		$this->result['remarks']=$customer->remarks;
//         		$this->result['customer_level']=$customer->customer_level;
//            }
//
//            $this->result['assign_log'] = AssignLog::where('member_id','=',$id)->get();
//            return response()->json($this->result);
//        }
//
//        //提交沟通记录
//        if($request->c=='selectlog_sub'){
//
//        	$data['member_id']=$request->member_id;
//			$data['comm_time']=$request->comm_time;
//			$data['contentlog']=$request->contentlog;
//			$data['admin_user_id']=Auth::guard('admin')->user()->id;
//			$data['created_at'] = Carbon::now()->toDateTimeString();
//			$data['updated_at'] = Carbon::now()->toDateTimeString();
//
//
//			if(strlen($request->nextcontact)>3){
//				$data_info['nextcontact']=$request->nextcontact;
//			}else if($request->nextcontact=='-1'){
//				$data_info['nextcontact']="2000-01-01";
//			}else{
//				$data_info['nextcontact']=Carbon::now()->addDays($request->nextcontact)->toDateTimeString();
//			}
//
//			$data_info['customer_level']=$request->customer_level;
//
//			$bool=Communicationlog::insert($data);
//			Customer::where("id","=",$data['member_id'])->update($data_info);
//
//			if(!$bool){
//				 $this->result['status']=1;
//			}
//			return response()->json($this->result);
//        }
//
//        //查询客户信息
//        if($request->c=='get_member'){
//        	$id=$request->id;
//			$data['data'] = Customer::from('members as c')
//			->select("c.*","au.name as auname",'c.source as source_name')
//	        ->leftJoin('admin_users as au','c.recommend','=','au.id')
//			->where('c.id','=',(int)$id)
//			->get();
//
//			$data['sources'] = MemberSource::get();
//			$data['levels'] = MemberLevel::get();
//			$data['projects'] = Project::get();
//			return response()->json($data);
//        }
//
//        //修改客户信息
//        if($request->c=='customer_upd'){
//        	$id = $request->id;
//			$data_upd['name'] = $request->name;
//			$data_upd['company'] = $request->company;
//			$data_upd['project'] = $request->project;
//			$data_upd['source'] = $request->source;
//			$data_upd['remarks'] = $request->remarks;
//			$data_upd['realname'] = $request->realname;
//			$data_upd['position'] = $request->position;
//			$data_upd['mobile'] = $request->mobile;
//			$data_upd['wechat'] = $request->wechat;
//			$data_upd['qq'] = $request->qq;
//			$data_upd['email'] = $request->email;
//
//			$bool = Customer::where('id','=',(int)$id)->update($data_upd);
//
//			if($bool){
//				return redirect('/admin/customer/index')->withSuccess('编辑成功！');
//			}else{
//				return redirect('/admin/customer/index')->withErrors('编辑失败！');
//			}
//        }
//
//        //提交激活信息
//        if($request->c=='activation_sub'){
//        	$id = $request->id;
//			$data_upd['name'] = $request->name;
//			$data_upd['type'] = $request->type;
//			$data_upd['company'] = $request->company;
//			$data_upd['project'] = $request->project;
//			$data_upd['project'] = $request->name;
//			$data_upd['customer_level'] = '成单签约';
//			$data_upd['realname'] = $request->realname;
//			$data_upd['position'] = $request->position;
//			$data_upd['mobile'] = $request->mobile;
//			$data_upd['wechat'] = $request->wechat;
//			$data_upd['qq'] = $request->qq;
//			$data_upd['email'] = $request->email;
//			$data_upd['cust_state'] = 0;
//			$data_upd['act_time'] = Carbon::now()->toDateTimeString();
//
//			$mobile = Members::queryInfo($data_upd["mobile"]);
//	        if($mobile && $mobile->id != $id){
//	            return redirect()->back()->withErrors('手机号已存在！');
//	        }
//
//	        $email = Members::queryEmail($data_upd["email"]);
//	        if($email && $email->id != $id){
//	            return redirect()->back()->withErrors('邮箱号已存在！');
//	        }
//
//			$bool=Members::where('id','=',(int)$id)->update($data_upd);
//
//			if($bool){
//				return redirect('/admin/member/index')->withSuccess('激活成功！');
//			}else{
//				return redirect('/admin/customer/index')->withErrors('激活失败！');
//			}
//        }
//
//        //查询用户信息
//        if($request->c=='getphome'){
//        	$customerinfo = CustomerInfo::from('members as c')
//        					->select('c.*','au.name as uname')
//        					->leftJoin('admin_users as au','c.recommend','=','au.id');
//
//      		if(trim($request->mobile)){
//           		$customerinfo->orwhere('c.mobile','=',$request->mobile);
//            }
//            if(trim($request->qq)){
//           		$customerinfo->orwhere('c.qq','=',$request->qq);
//            }
//            if(trim($request->wechat)){
//           		$customerinfo->orwhere('c.wechat','=',$request->wechat);
//            }
//            if(trim($request->email)){
//           		$customerinfo->orwhere('c.email','=',$request->email);
//            }
//
//          	if(!trim($request->mobile)&&!trim($request->qq)&&!trim($request->wechat)&&!trim($request->email)){
//          		$this->result['status']=1;
//          		$this->result['msg'] = '请填写一个联系方式！！！';
//          		return response()->json($this->result);
//          	}
//
//          	$bool = $customerinfo->first();
//        	if($bool){
//				$this->result['status']=1;
//				if($bool->mobile==$request->mobile&&trim($request->mobile)!=''){
//					$this->result['msg'] = '该手机已经被注册了，此客户已分配给：'.$bool->uname;
//				}
//				if($bool->qq==$request->qq&&trim($request->qq)!=''){
//					$this->result['msg'] = '该QQ已经被注册了，此客户已分配给：'.$bool->uname;
//				}
//				if($bool->wechat==$request->wechat&&trim($request->wechat)!=''){
//					$this->result['msg'] = '该微信已经被注册了，此客户已分配给：'.$bool->uname;
//				}
//				if($bool->email==$request->email&&trim($request->email)!=''){
//					$this->result['msg'] = '该邮箱已经被注册了，此客户已分配给：'.$bool->uname;
//				}
//			}
//			return response()->json($this->result);
//        }
//    }
//
//	//get跳转函数
//	public function getdownload(Request $request){
//		$data = $request->input();
//
//		//下载导入模板
//		if(isset($data['c']) && $data['c']=='demo'){
//			$fileName = 'demo';
//	    	if(is_file(realpath(base_path('public/download')).'/'.$fileName.'.xlsx')){
//	        	return response()->download(realpath(base_path('public/download')).'/'.$fileName.'.xlsx',$fileName.'.xlsx');
//		    }else{
//		     	return redirect('/admin/customer');
//		    }
//		}
//
//		//导出客户列表
//		if(isset($data['c']) && $data['c']=='pldownload'){
//
//			$total = Customer::from('members as c')
//					->select('c.*','au.name as admin_name')
//					->leftJoin('admin_users as au','c.recommend','=','au.id');
//
//	        //权限判断
//			if(trim(Auth::guard('admin')->user()->power)!=''){
//				$arr = explode(',',Auth::guard('admin')->user()->power);
//				$total->whereIn('au.branch_id',$arr);
//			}else{
//				$total->where('au.id','=',Auth::guard('admin')->user()->id);
//			}
//
//	        //判断自定义搜索条件
//	        if(trim($request->source) != ""){
//				$total->where('c.source','=',$request->source);
//			}
//	        if(trim($request->cust_state) != ""){
//				$total->where('c.cust_state','=',$request->cust_state);
//			}
//			if(trim($request->recommend) != ""){
//				$total->where('c.recommend','=',$request->recommend);
//			}
//			if(trim($request->customer_level) != ""){
//				$total->where('c.customer_level','=',$request->customer_level);
//			}
//			if(trim($request->customer_level) != ""){
//				$total->where('c.customer_level','=',$request->customer_level);
//			}
//			if(trim($request->nexttime1)!=''){
//				$nexttime1 = substr(trim($request->nexttime1),0,10).' 00:00:00';
//				$nexttime2 = substr(trim($request->nexttime1),13,10).' 23:59:59';
//
//				$total->whereBetween('c.nextcontact',[$nexttime1,$nexttime2]);
//			}
//			if(trim($request->created_at)!=''){
//				$created_at1 = substr(trim($request->created_at),0,10).' 00:00:00';
//				$created_at2 = substr(trim($request->created_at),13,10).' 23:59:59';
//
//				$total->whereBetween('c.created_at',[$created_at1,$created_at2]);
//			}
//
//			$sosodoc = trim($request->sosodoc);
//			if($sosodoc != ''){
//				$total->where(function ($query) use ($sosodoc) {
//                    $query->where('c.realname', 'LIKE', '%' . $sosodoc . '%')
//		                ->orwhere('c.company', 'LIKE', '%' . $sosodoc . '%')
//		                ->orwhere('c.mobile', 'LIKE', '%' . $sosodoc . '%')
//		                ->orwhere('c.project', 'LIKE', '%' . $sosodoc . '%');
//               });
//			}
//
//	        $obj = $total->get();
//
//	        $arr=[['ID','名称','手机','微信','qq','邮箱','类型','客户意向度','公司','指派','上传者','来源','项目','注册时间','下次联系','备注','沟通记录']];
//
//			foreach($obj as $key => $val){
//				if($key != 0 && $val->id == $obj[$key-1]->id){
//					$len=count($arr)-1;
//					$arr[$len][16]=$arr[$len][16]."\r\n".$val->comm_time.'  '.$val->contentlog;
//				}else{
//					if($val->type==0){
//						$type="个人";
//					}else{
//						$type="企业";
//					}
//
//					$arr[]=array(
//						$val->id,
//						$val->realname,
//						$val->mobile,
//						$val->wechat,
//						$val->qq,
//						$val->email,
//						$type,
//						$val->customer_level,
//						$val->company,
//						$val->auname,
//						$val->addperson,
//						$val->source,
//						$val->project,
//						$val->created_at,
//						$val->nextcontact,
//						$val->remarks,
//						$val->comm_time.'  '.$val->contentlog
//					);
//				}
//			}
//            $con = Configs::first();
//            $env = $con->env;
//            if ($env == 'CLOUD'){
//                $temp_file = tempnam(sys_get_temp_dir(),"1xlsx");  //临时文件
//                $a = Excel::create('客户信息',function($excel) use ($arr){
//                    $excel->sheet('客户信息', function($sheet) use ($arr){
//                        $sheet->rows($arr);
//                    });
//                })->string('xlsx');
//                file_put_contents($temp_file,$a);
//                $data['code'] = 3;
//                $data['name'] = '客户信息.xlsx';
//                $data['data'] = $temp_file;
//                return $data;
//            }else{
//                Excel::create('客户信息',function($excel) use ($arr){
//                    $excel->sheet('客户信息', function($sheet) use ($arr){
//                        $sheet->rows($arr);
//                    });
//                })->export('xlsx');
//            }
//		}
//		return redirect('/admin/customer');
//	}
//
//	//指派记录函数
//	public function assign_log($mid,$assign_name=''){
//		$data_assign['member_id']=$mid;
//		$data_assign['assign_name']=$assign_name;
//		$data_assign['assign_admin']=Auth::guard('admin')->user()->name;
//		$data_assign['updated_at']=Carbon::now();
//		$data_assign['created_at']=Carbon::now();
//
//        $bool = AssignLog::insertGetId($data_assign);
//        return $bool;
//	}
//
//	//用户删除
//    public function destroy($id)
//    {
//        $tag = Customer::find((int)$id);
//        $tag1 = CustomerInfo::where("member_id","=",(int)$id)->delete();
//        if($tag->delete()){
//            return redirect()->back()->withSuccess("删除成功");
//        }
//
//    }
//
//	//邮件发送函数
//	public function testmail($name,$to,$member,$tille="指派客户通知"){
//		$admin = Auth::guard('admin')->user()->name;
//
//		$em1 = "wstianxia.com";
//		$em2 = "wegouer.com";
//		$em3 = "netbcloud.com";
//		$em4 = "wangqudao.com";
//		$em5 = "qcloud0755.com";
//
//		if(strpos($to,$em1)||strpos($to,$em2)||strpos($to,$em3)||strpos($to,$em4)||strpos($to,$em5)){
//
//		}else{
//			return false;
//		}
//
//        $flag = Mail::send('admin.test',['name'=>$name,'admin'=>$admin,'member'=>$member],function($message)use($to,$tille){
//            $message->to($to)->subject($tille);
//        });
//        if(count(Mail::failures()) < 1){
//            return true;
//        }else{
//            return false;
//        }
//	}
//
//	//企业微信自动推送函数
//	public function ese_wechat($user_name,$content){
//		if(trim($user_name)==''){
//			return false;
//		}
//
//		if(!Cache::has('xiaoxi_access_token')){
//			$con = Configs::first();
//			$res = file_get_contents("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$con->company_id."&corpsecret=".$con->push_secret);
//			$arr = json_decode($res,true);
//			Cache::add('xiaoxi_access_token',$arr['access_token'],120);//键 值 有效时间（分钟）
//
//			$url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".Cache::get('xiaoxi_access_token');
//
//			$data =array(
//				"touser" => $user_name,
//			    "msgtype" => "text",
//			    "agentid" => 1000011,
//			    "text" => array(
//			        "content" => $content
//			    ),
//			   "safe"=> 0
//			);
//			$data = json_encode($data);
//			$res = $this->curl($url,$data);
//			if(!$res){
//				return false;
//			}else{
//				return true;
//			}
//
//		}else{
//			$url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".Cache::get('xiaoxi_access_token');
//
//			$data =array(
//				"touser" => $user_name,
//			    "msgtype" => "text",
//			    "agentid" => 1000011,
//			    "text" => array(
//			        "content" => $content
//			    ),
//			   "safe"=> 0
//			);
//			$data = json_encode($data);
//			$res = $this->curl($url,$data);
//			if(!$res){
//				return false;
//			}else{
//				return true;
//			}
//		}
//	}
//
//	//curl函数
//	public function  curl($url,$data){
//        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//		curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
//		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//		    'Content-Type: application/json',
//		    'Content-Length: ' . strlen($data)
//		));
//		$output = curl_exec($ch);
//		if (curl_errno($ch)) {
//		    return false;
//		}
//		curl_close($ch);
//        return $output;
//    }
//
//}
//
