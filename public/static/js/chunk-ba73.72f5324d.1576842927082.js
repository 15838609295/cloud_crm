(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-ba73"],{J1hZ:function(t,e,s){"use strict";var n=s("TF6R");s.n(n).a},TF6R:function(t,e,s){},jRJo:function(t,e,s){"use strict";s.r(e);var n={data:function(){return{versions:[]}},watch:{},created:function(){this.getData()},methods:{getData:function(t){var e=this;this.tableLoading=!0,t||(t={}),this.$store.dispatch("GetConnect",{url:"sysupdate/list"}).then(function(t){e.versions=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})}}},a=(s("J1hZ"),s("KHd+")),r=Object(a.a)(n,function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"app-container bg-gray"},[s("el-row",[s("el-col",{attrs:{xs:24,sm:24,md:24,lg:22,xl:16}},t._l(t.versions,function(e,n){return s("el-card",{key:n,staticClass:"box-1"},[s("div",{staticClass:"header",attrs:{slot:"header"},slot:"header"},[s("span",[t._v(t._s(e.version))]),t._v(" "),s("div",[t._v(t._s(new Date(1e3*parseInt(e.create_time)).toLocaleString().replace(/:\d{1,2}$/," ")))])]),t._v(" "),s("el-tag",{attrs:{type:"primary"}},[t._v(t._s(e.tips))]),t._v(" "),s("div",{staticClass:"content",domProps:{innerHTML:t._s(e.introduce)}},[t._v(t._s(e.introduce))])],1)}))],1)],1)},[],!1,null,"070f7e26",null);r.options.__file="version.vue";e.default=r.exports}}]);