(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-4120"],{"5mFd":function(t,e,r){},"b/jd":function(t,e,r){"use strict";var a=r("5mFd");r.n(a).a},csLp:function(t,e,r){"use strict";r.r(e);var a=r("P2sY"),o=r.n(a),l={data:function(){return{ImageUrl:"",form:{id:"",name:"",sex:"男",mobile:"",email:"",type:"",avatar:"",level:""}}},created:function(){this.loadInfo(this.$route.query.id)},methods:{loadInfo:function(t){var e=this,r="activity/proposalInfo/"+t;this.$store.dispatch("GetConnect",{url:r}).then(function(t){o()(e.form,t.data)}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})}}},s=(r("b/jd"),r("KHd+")),i=Object(s.a)(l,function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("div",{staticClass:"app-container bg-gray"},[r("el-card",{staticClass:"box-1"},[r("div",{attrs:{slot:"header"},slot:"header"},[r("span",[t._v("会员详情")])]),t._v(" "),r("el-form",{ref:"form",attrs:{model:t.form,"label-width":"160px"}},[r("el-form-item",{attrs:{label:"用户名："}},[t._v(t._s(t.form.name))]),t._v(" "),r("el-form-item",{attrs:{label:"公司："}},[t._v(t._s(t.form.company))]),t._v(" "),r("el-form-item",{attrs:{label:"职位："}},[t._v(t._s(t.form.position))]),t._v(" "),r("el-form-item",{attrs:{label:"手机："}},[t._v(t._s(t.form.mobile))]),t._v(" "),r("el-form-item",{attrs:{label:"微信："}},[t._v(t._s(t.form.wechat))]),t._v(" "),r("el-form-item",{attrs:{label:"QQ："}},[t._v(t._s(t.form.qq))]),t._v(" "),r("el-form-item",{attrs:{label:"注册时间："}},[t._v(t._s(t.form.create_time))]),t._v(" "),r("el-form-item",{attrs:{label:"用户等级："}},[t._v(t._s(t.form.level_name))])],1)],1),t._v(" "),r("el-card",{staticClass:"box-2"},[r("div",{attrs:{slot:"header"},slot:"header"},[r("span",[t._v("留言内容")])]),t._v(" "),r("el-form",{ref:"form",attrs:{model:t.form,"label-width":"20px"}},[r("el-form-item",{staticStyle:{color:"#b7b7b7"}},[t._v(t._s(t.form.content))]),t._v(" "),r("el-form-item",t._l(t.form.picture_list,function(e){return r("ws-show-img",{key:e,staticStyle:{width:"120px",height:"120px","margin-right":"20px"},attrs:{src:t.ImageUrl+e}})}))],1)],1)],1)},[],!1,null,"2deb3bbc",null);i.options.__file="commentDetails.vue";e.default=i.exports}}]);