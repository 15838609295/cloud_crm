(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-0b68"],{I74F:function(e,t,a){"use strict";var r=a("TmXA");a.n(r).a},TmXA:function(e,t,a){},plL6:function(e,t,a){"use strict";a.r(t);var r={data:function(){var e=this;return{title:"",teamList:[],companyList:[],roleList:[],form:{name:"",sex:"男",mobile:"",email:"",wechat_id:"",company_id:"",branch:"",team_array:[],position:"",role_id:"",hiredate:"",formal_time:"",password:"",checkPass:"",ach_status:1,status:0,set_qy_wechat:0,power:"",powerArray:[]},formRules:{name:[{required:!0,message:"请输入用户名",trigger:"change"}],mobile:[{required:!0,message:"请填写手机号码",trigger:"change"}],email:[{required:!0,message:"请输入邮箱",trigger:"change"}],wechat_id:[{required:!0,message:"请输入企业邮箱账号",trigger:"change"}],company_id:[{required:!0,message:"请选择所属部门",trigger:"change"}],role_id:[{required:!0,message:"请选择所属角色",trigger:"change"}],position:[{required:!0,message:"请输入职位",trigger:"change"}],hiredate:[{required:!0,message:"请选择入职时间",trigger:"change"}],password:[{validator:function(t,a,r){1===e.form.set_qy_wechat&&(""===a?r(new Error("请输入密码")):(""!==e.form.checkPass&&e.$refs.form.validateField("checkPass"),r())),r()},trigger:"blur"}],checkPass:[{validator:function(t,a,r){1===e.form.set_qy_wechat&&(""===a?r(new Error("请再次输入密码")):a!==e.form.password?r(new Error("两次输入密码不一致!")):r()),r()},trigger:"blur"}]}}},watch:{},created:function(){this.title=this.$route.query.title,this.form.hiredate=new Date,this.$route.query.id&&(this.id=this.$route.query.id,this.getUserInfo(this.$route.query.id)),this.getTeamList(),this.getCompanyList(),this.getRoleList()},methods:{getUserInfo:function(e){var t=this,a="user/detail/"+e;this.$store.dispatch("GetConnect",{url:a}).then(function(e){var a={name:e.data.name,sex:e.data.sex,mobile:e.data.mobile,email:e.data.email,wechat_id:e.data.wechat_id,company_id:e.data.company_id,team_array:e.data.user_branch,position:e.data.position,hiredate:e.data.hiredate,formal_time:e.data.formal_time,ach_status:e.data.ach_status,set_qy_wechat:e.data.set_qy_wechat,powerArray:e.data.power,role_id:e.data.role_id,status:e.data.status};t.form=a}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员")})},getTeamList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"branch/all-list"}).then(function(t){e.teamList=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},getCompanyList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"company/all-list"}).then(function(t){e.companyList=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},getRoleList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"role/all-list"}).then(function(t){e.roleList=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},submitForm:function(e){var t=this;this.$refs[e].validate(function(e){if(e){var a=t.form;a.power=a.powerArray.join(","),a.branch=a.team_array.join(",");var r=t.id?"user/edit/"+t.id:"user/create";t.$store.dispatch("GetConnect",{url:r,data:a}).then(function(e){t.$message.success(e.msg),t.$router.back(-1)}).catch(function(e){t.$message.error(e.msg)})}else t.$message.error("提交失败,请检查必填项")})},resetForm:function(e){this.$refs[e].resetFields()}}},o=(a("I74F"),a("KHd+")),s=Object(o.a)(r,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("el-card",{staticClass:"box-1"},[a("div",{attrs:{slot:"header"},slot:"header"},[a("span",[e._v(e._s(e.title))])]),e._v(" "),a("el-form",{ref:"form",attrs:{model:e.form,rules:e.formRules,"label-width":"160px"}},[a("el-form-item",{attrs:{label:"用户名称",prop:"name"}},[a("el-input",{model:{value:e.form.name,callback:function(t){e.$set(e.form,"name",t)},expression:"form.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"性别",prop:"sex"}},[a("el-radio-group",{model:{value:e.form.sex,callback:function(t){e.$set(e.form,"sex",t)},expression:"form.sex"}},[a("el-radio",{attrs:{label:"男"}}),e._v(" "),a("el-radio",{attrs:{label:"女"}})],1)],1),e._v(" "),a("el-form-item",{attrs:{label:"手机",prop:"mobile"}},[a("el-input",{model:{value:e.form.mobile,callback:function(t){e.$set(e.form,"mobile",t)},expression:"form.mobile"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"邮箱",prop:"email"}},[a("el-input",{model:{value:e.form.email,callback:function(t){e.$set(e.form,"email",t)},expression:"form.email"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"企业微信账号"}},[a("el-input",{model:{value:e.form.wechat_id,callback:function(t){e.$set(e.form,"wechat_id",t)},expression:"form.wechat_id"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"部门",prop:"company_id"}},[a("el-select",{attrs:{placeholder:"请选择所属部门",clearable:""},model:{value:e.form.company_id,callback:function(t){e.$set(e.form,"company_id",t)},expression:"form.company_id"}},e._l(e.companyList,function(e){return a("el-option",{key:e.id,attrs:{label:e.name,value:e.id}})}))],1),e._v(" "),a("el-form-item",{attrs:{label:"团队",prop:"team_array"}},[a("el-select",{staticClass:"teamSelect",attrs:{placeholder:"请选择所属团队",clearable:"",multiple:""},model:{value:e.form.team_array,callback:function(t){e.$set(e.form,"team_array",t)},expression:"form.team_array"}},e._l(e.teamList,function(e){return a("el-option",{key:e.id,attrs:{label:e.branch_name,value:e.id}})}))],1),e._v(" "),a("el-form-item",{attrs:{label:"职位",prop:"position"}},[a("el-input",{model:{value:e.form.position,callback:function(t){e.$set(e.form,"position",t)},expression:"form.position"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"入职时间",prop:"hiredate"}},[a("el-date-picker",{attrs:{type:"date","value-format":"yyyy-MM-dd",placeholder:"选择日期"},model:{value:e.form.hiredate,callback:function(t){e.$set(e.form,"hiredate",t)},expression:"form.hiredate"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"转正时间"}},[a("el-date-picker",{attrs:{type:"date","value-format":"yyyy-MM-dd",placeholder:"选择日期"},model:{value:e.form.formal_time,callback:function(t){e.$set(e.form,"formal_time",t)},expression:"form.formal_time"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"密码",prop:"password"}},[a("el-input",{model:{value:e.form.password,callback:function(t){e.$set(e.form,"password",t)},expression:"form.password"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"确认密码",prop:"checkPass"}},[a("el-input",{model:{value:e.form.checkPass,callback:function(t){e.$set(e.form,"checkPass",t)},expression:"form.checkPass"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"是否显示业绩"}},[a("el-radio-group",{model:{value:e.form.ach_status,callback:function(t){e.$set(e.form,"ach_status",t)},expression:"form.ach_status"}},[a("el-radio",{attrs:{label:0}},[e._v("显示")]),e._v(" "),a("el-radio",{attrs:{label:1}},[e._v("隐藏")])],1)],1),e._v(" "),a("el-form-item",{attrs:{label:"用户状态"}},[a("el-radio-group",{model:{value:e.form.status,callback:function(t){e.$set(e.form,"status",t)},expression:"form.status"}},[a("el-radio",{attrs:{label:0}},[e._v("启用")]),e._v(" "),a("el-radio",{attrs:{label:1}},[e._v("禁用")])],1)],1),e._v(" "),"编辑用户"!==e.title?a("el-form-item",{attrs:{label:"是否创建企业微信"}},[a("el-radio-group",{model:{value:e.form.set_qy_wechat,callback:function(t){e.$set(e.form,"set_qy_wechat",t)},expression:"form.set_qy_wechat"}},[a("el-radio",{attrs:{label:1}},[e._v("创建")]),e._v(" "),a("el-radio",{attrs:{label:0}},[e._v("不创建")])],1)],1):e._e(),e._v(" "),a("el-form-item",{attrs:{label:"管理权限"}},[a("el-checkbox-group",{model:{value:e.form.powerArray,callback:function(t){e.$set(e.form,"powerArray",t)},expression:"form.powerArray"}},e._l(e.teamList,function(t){return a("el-checkbox",{key:t.id,attrs:{label:t.id}},[e._v(e._s(t.branch_name))])}))],1),e._v(" "),a("el-form-item",{attrs:{label:"角色列表"}},[a("el-radio-group",{model:{value:e.form.role_id,callback:function(t){e.$set(e.form,"role_id",t)},expression:"form.role_id"}},e._l(e.roleList,function(t){return a("el-radio",{key:t.id,attrs:{label:t.id}},[e._v(e._s(t.name))])}))],1),e._v(" "),a("div",{staticStyle:{"text-align":"center"}},[a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.submitForm("form")}}},[e._v(e._s("编辑用户"===e.title?"保存":"立即创建"))]),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.$router.back(-1)}}},[e._v("取消")])],1)],1)],1)],1)},[],!1,null,"b5b67cb8",null);s.options.__file="modifyUser.vue";t.default=s.exports}}]);