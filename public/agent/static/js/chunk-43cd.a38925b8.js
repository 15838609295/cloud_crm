(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-43cd"],{"2CEh":function(e,t,a){},lyo0:function(e,t,a){"use strict";a.r(t);var r={data:function(){return{ImageUrl:this.$store.getters.serverUrl,dialogImageUrl:"",pageType:0,title:"个人信息",form:{avatar:""},formRules:{name:[{required:!0,message:"请输入名称",trigger:"blur"}]}}},created:function(){this.getUserInfo()},methods:{getUserInfo:function(){var e=this;this.$store.dispatch("GetConnect",{url:"member/index"}).then(function(t){e.form=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},handleAvatarChange:function(e){var t=e.name;/(.jpg|.jpeg|.gif|.png|.bmp)$/.test(t.toLowerCase())||this.$message.error("请选择图片文件")},upload_image:function(e){var t=this,a={picture:e.file};this.$store.dispatch("UploadFile",{url:"web/ajax/upload",data:a}).then(function(e){t.form.avatar=e.data.url}).catch(function(e){t.$message.error(e.msg)})},toModify:function(){this.pageType=1,this.title="编辑信息"},submitForm:function(e){var t=this;this.$refs[e].validate(function(e){if(e){var a=t.form;t.$store.dispatch("GetConnect",{url:"member/update",data:a}).then(function(e){t.$message.success(e.msg),t.pageType=0;var a=t.form.avatar;t.$store.dispatch("SetAvatar",{avatar:a})}).catch(function(e){t.$message.error(e.msg)})}else t.$message.error("提交失败,请检查必填项")})},resetForm:function(e){this.$refs[e].resetFields()}}},o=(a("urWm"),a("KHd+")),s=Object(o.a)(r,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-card",{staticClass:"box-1"},[a("div",{staticClass:"header",attrs:{slot:"header"},slot:"header"},[a("span",[e._v(e._s(e.title))]),e._v(" "),0===e.pageType?a("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small"},on:{click:e.toModify}},[e._v("编辑")]):e._e()],1),e._v(" "),a("el-form",{ref:"form",attrs:{model:e.form,rules:e.formRules,"label-width":"160px"}},[a("el-form-item",{attrs:{label:"用户名：",prop:"name"}},[1===e.pageType?a("el-input",{model:{value:e.form.name,callback:function(t){e.$set(e.form,"name",t)},expression:"form.name"}}):a("span",[e._v(e._s(e.form.name||""))])],1),e._v(" "),a("el-form-item",{attrs:{label:"头像："}},[a("el-upload",{ref:"upload",staticClass:"avatar-uploader",attrs:{"show-file-list":!1,"http-request":e.upload_image,"on-change":e.handleAvatarChange,disabled:!e.pageType,action:""}},[e.form.avatar?a("img",{staticClass:"avatar",attrs:{src:e.ImageUrl+e.form.avatar}}):a("i",{staticClass:"el-icon-plus avatar-uploader-icon"})])],1),e._v(" "),a("el-form-item",{attrs:{label:"手机号："}},[1===e.pageType?a("el-input",{model:{value:e.form.mobile,callback:function(t){e.$set(e.form,"mobile",t)},expression:"form.mobile"}}):a("span",[e._v(e._s(e.form.mobile||""))])],1),e._v(" "),a("el-form-item",{attrs:{label:"公司："}},[1===e.pageType?a("el-input",{model:{value:e.form.company,callback:function(t){e.$set(e.form,"company",t)},expression:"form.company"}}):a("span",[e._v(e._s(e.form.company||""))])],1),e._v(" "),a("el-form-item",{attrs:{label:"职位："}},[1===e.pageType?a("el-input",{model:{value:e.form.position,callback:function(t){e.$set(e.form,"position",t)},expression:"form.position"}}):a("span",[e._v(e._s(e.form.position||""))])],1),e._v(" "),a("el-form-item",{attrs:{label:"邮箱："}},[1===e.pageType?a("el-input",{model:{value:e.form.email,callback:function(t){e.$set(e.form,"email",t)},expression:"form.email"}}):a("span",[e._v(e._s(e.form.email||""))])],1),e._v(" "),a("el-form-item",{attrs:{label:"QQ："}},[1===e.pageType?a("el-input",{model:{value:e.form.qq,callback:function(t){e.$set(e.form,"qq",t)},expression:"form.qq"}}):a("span",[e._v(e._s(e.form.qq||""))])],1),e._v(" "),a("el-form-item",{attrs:{label:"微信："}},[1===e.pageType?a("el-input",{model:{value:e.form.wechat,callback:function(t){e.$set(e.form,"wechat",t)},expression:"form.wechat"}}):a("span",[e._v(e._s(e.form.wechat||""))])],1),e._v(" "),1===e.pageType?a("div",{staticStyle:{"text-align":"center"}},[a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.submitForm("form")}}},[e._v("保存")]),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.title="个人信息",e.pageType=0}}},[e._v("取消")])],1):e._e()],1)],1)],1)},[],!1,null,"4ccc1b09",null);s.options.__file="index.vue";t.default=s.exports},urWm:function(e,t,a){"use strict";var r=a("2CEh");a.n(r).a}}]);