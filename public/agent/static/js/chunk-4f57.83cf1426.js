(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-4f57"],{EXiG:function(t,e,n){},"V2X+":function(t,e,n){"use strict";var i=n("EXiG");n.n(i).a},c11S:function(t,e,n){"use strict";var i=n("gTgX");n.n(i).a},gTgX:function(t,e,n){},ntYl:function(t,e,n){"use strict";n.r(e);var i={name:"Login",data:function(){return{siteTitle:"",qy_redirect:"",agent_url:"",codeDialogVisible:!1,loginForm:{username:"",password:""},loginRules:{username:[{required:!0,trigger:"blur",validator:function(t,e,n){n()}}],password:[{required:!0,trigger:"blur",validator:function(t,e,n){n()}}]},loading:!1,pwdType:"password"}},created:function(){this.initSiteTitle()},methods:{initSiteTitle:function(){var t=this;this.$store.dispatch("GetConnect",{url:"admin/common/getTitle"}).then(function(e){t.siteTitle=e.data.title,t.qy_redirect=e.data.qy_redirect,t.agent_url=e.data.agent_url}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员")})},showPwd:function(){"password"===this.pwdType?this.pwdType="":this.pwdType="password"},handleLogin:function(){var t=this;this.$refs.loginForm.validate(function(e){e&&(t.loading=!0,t.$store.dispatch("Login",t.loginForm).then(function(){t.loading=!1,t.$router.push({path:"/"})}).catch(function(e){t.loading=!1,t.$message.error(e.msg)}))})},showQRCode:function(){window.open(this.agent_url)},showCodeDialog:function(){this.codeDialogVisible=!0}}},o=(n("c11S"),n("V2X+"),n("KHd+")),s=Object(o.a)(i,function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"login-container"},[i("el-form",{ref:"loginForm",staticClass:"login-form",attrs:{model:t.loginForm,rules:t.loginRules,"auto-complete":"on","label-position":"left"}},[i("h3",{staticClass:"title"},[t._v(t._s("合作伙伴管理平台"))]),t._v(" "),i("el-form-item",{attrs:{prop:"username"}},[i("span",{staticClass:"svg-container svg-container_login"},[i("svg-icon",{attrs:{"icon-class":"user"}})],1),t._v(" "),i("el-input",{attrs:{name:"username",type:"text","auto-complete":"on",placeholder:"用户名"},model:{value:t.loginForm.username,callback:function(e){t.$set(t.loginForm,"username",e)},expression:"loginForm.username"}})],1),t._v(" "),i("el-form-item",{attrs:{prop:"password"}},[i("span",{staticClass:"svg-container"},[i("svg-icon",{attrs:{"icon-class":"password"}})],1),t._v(" "),i("el-input",{attrs:{type:t.pwdType,name:"password","auto-complete":"on",placeholder:"密码"},nativeOn:{keyup:function(e){return"button"in e||!t._k(e.keyCode,"enter",13,e.key,"Enter")?t.handleLogin(e):null}},model:{value:t.loginForm.password,callback:function(e){t.$set(t.loginForm,"password",e)},expression:"loginForm.password"}}),t._v(" "),i("span",{staticClass:"show-pwd",on:{click:t.showPwd}},[i("svg-icon",{attrs:{"icon-class":"eye"}})],1)],1),t._v(" "),i("el-form-item",[i("el-button",{staticStyle:{width:"100%"},attrs:{loading:t.loading,type:"primary"},nativeOn:{click:function(e){return e.preventDefault(),t.handleLogin(e)}}},[t._v("\n        登录\n      ")])],1),t._v(" "),i("div",{staticClass:"bottom-text"},[i("span",{on:{click:t.showCodeDialog}},[t._v("小程序二维码")])])],1),t._v(" "),i("el-dialog",{attrs:{visible:t.codeDialogVisible,"modal-append-to-body":!1,title:"请使用微信扫码打开",width:"378px"},on:{"update:visible":function(e){t.codeDialogVisible=e}}},[i("div",{staticClass:"iframe"},[i("img",{attrs:{src:n("sgnr")}})])])],1)},[],!1,null,"0e7ab53c",null);s.options.__file="index.vue";e.default=s.exports},sgnr:function(t,e,n){t.exports=n.p+"static/img/code.6ef528a.jpg"}}]);