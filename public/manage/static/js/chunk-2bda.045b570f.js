(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-2bda"],{dCRr:function(e,t,n){"use strict";n.r(t);var a=n("14Xm"),s=n.n(a),r=n("D3Ub"),i=n.n(r),o={data:function(){return{isCloud:!1}},mounted:function(){this.loadData()},methods:{loadData:function(){var e=this;i()(s.a.mark(function t(){var n;return s.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,e.$service.adminGetSetting();case 2:0===(n=t.sent).code?e.isCloud="CLOUD"===n.data.env:e.$message.error(n.msg);case 4:case"end":return t.stop()}},t,e)}))()},changeIsCloud:function(){var e=this,t=!0===this.isCloud?"CLOUD":"LOCAL";i()(s.a.mark(function n(){var a;return s.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return n.next=2,e.$service.adminUpdateIsCloudStatus({env:t});case 2:0===(a=n.sent).code?e.$message.success("操作成功~"):e.$message.error(a.msg);case 4:case"end":return n.stop()}},n,e)}))()}}},c=(n("qO0S"),n("KHd+")),u=Object(c.a)(o,function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("div",{staticClass:"app-container pagination"},[n("el-card",{staticClass:"box-1"},[n("div",{attrs:{slot:"header"},slot:"header"},[n("span",[e._v("系统设置")])]),e._v(" "),n("el-form",{attrs:{"label-width":"160px"}},[n("el-form-item",{attrs:{label:"是否云开发版本："}},[n("el-switch",{on:{change:e.changeIsCloud},model:{value:e.isCloud,callback:function(t){e.isCloud=t},expression:"isCloud"}})],1)],1)],1)],1)},[],!1,null,"1325364e",null);u.options.__file="index.vue";t.default=u.exports},mLiG:function(e,t,n){},qO0S:function(e,t,n){"use strict";var a=n("mLiG");n.n(a).a}}]);