(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-19dc"],{"0kCQ":function(t,n,s){},sffB:function(t,n,s){"use strict";s.r(n);var e={data:function(){return{form:{content:""}}},watch:{},created:function(){this.getData()},methods:{getData:function(){var t=this;this.$store.dispatch("GetConnect",{url:"lookhelp/index"}).then(function(n){t.form=n.data}).catch(function(n){t.$message.error(n.msg+",请刷新或联系管理员")})},toModify:function(){this.$router.push({path:"/information/instructions_edit"})}}},o=(s("soAm"),s("KHd+")),a=Object(o.a)(e,function(){var t=this,n=t.$createElement,s=t._self._c||n;return s("div",{staticClass:"app-container  bg-gray"},[s("el-card",{staticClass:"box-1"},[s("div",{attrs:{slot:"header"},slot:"header"},[s("span",[t._v("使用帮助")]),t._v(" "),t.queryMatch(49)?s("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small"},on:{click:t.toModify}},[t._v("编辑")]):t._e()],1),t._v(" "),s("div",{staticClass:"vhtml",domProps:{innerHTML:t._s(t.form.content)}},[t._v(t._s(t.form.content))])])],1)},[],!1,null,"577ac50f",null);a.options.__file="instructions.vue";n.default=a.exports},soAm:function(t,n,s){"use strict";var e=s("0kCQ");s.n(e).a}}]);