(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-4149"],{V0q3:function(t,e,s){},dlbD:function(t,e,s){"use strict";s.r(e);var i={data:function(){return{isShow:!1,ImageUrl:"",info:{}}},watch:{$route:function(t){var e=this;"企业展示"===t.name?(this.getData(),setTimeout(function(){e.isShow=!0},500)):this.isShow=!1}},created:function(){"企业展示"===this.$route.name&&(this.isShow=!0,this.getData())},methods:{getData:function(){var t=this;this.$store.dispatch("GetConnect",{url:"workorder/enterprise"}).then(function(e){t.info=e.data}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员")})},toModify:function(){this.$router.push({path:"/information/display/modify"})}}},a=(s("kpyr"),s("KHd+")),r=Object(a.a)(i,function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"app-container bg-gray"},[s("transition",{attrs:{name:"fade-transform",mode:"out-in"}},[s("router-view")],1),t._v(" "),t.isShow?s("el-card",{staticClass:"box-1"},[s("div",{attrs:{slot:"header"},slot:"header"},[s("span",[t._v("企业展示")]),t._v(" "),t.queryMatch(212)?s("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small"},on:{click:t.toModify}},[t._v("编辑")]):t._e()],1),t._v(" "),s("el-form",{attrs:{"label-width":"160px"}},[s("el-form-item",{attrs:{label:"企业名称：",prop:"title"}},[t._v(t._s(t.info.name))]),t._v(" "),s("el-form-item",{attrs:{label:"略缩图："}},[s("ws-img",{staticStyle:{height:"160px"},attrs:{src:t.info.picture?t.ImageUrl+t.info.picture:""}})],1),t._v(" "),s("el-form-item",{attrs:{label:"企业简介："}},[s("pre",{staticClass:"pre"},[t._v(t._s(t.info.introduce))])]),t._v(" "),s("el-form-item",{attrs:{label:"服务电话："}},[t._v(t._s(t.info.tel))]),t._v(" "),s("el-form-item",{attrs:{label:"联系地址："}},[s("pre",{staticClass:"pre"},[t._v(t._s(t.info.address))])])],1)],1):t._e()],1)},[],!1,null,"7a3585b6",null);r.options.__file="index.vue";e.default=r.exports},kpyr:function(t,e,s){"use strict";var i=s("V0q3");s.n(i).a}}]);