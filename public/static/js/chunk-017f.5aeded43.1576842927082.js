(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-017f"],{"2V0E":function(e,t,a){"use strict";var r=a("TjP9");a.n(r).a},TjP9:function(e,t,a){},z5C5:function(e,t,a){"use strict";a.r(t);var r=a("zAZ/"),n={data:function(){return{count:["一","二","三","四","五"],isShow:!1,searchTerms:{type:1,level:"",searchValue:""},tableMaxHeight:document.documentElement.clientHeight-300,tableLoading:!1,currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,pageSizes:[10,20,50,100,1e3],total:0,tableData:[]}},watch:{$route:function(e){var t=this;"售后管理"===e.name?(this.getData(),setTimeout(function(){t.isShow=!0},500)):this.isShow=!1}},created:function(){"售后管理"===this.$route.name&&(this.isShow=!0,this.getData())},methods:{handleCurrentChange:r.c,handleSizeChange:r.d,handleSortChange:r.e,getData:function(e){var t=this;this.tableLoading=!0,e||(e={});var a={pageNo:this.currentPage,pageSize:this.pageSize,search:this.searchTerms.searchValue,type:this.searchTerms.type,star_class:this.searchTerms.level};e.sortName&&(a.sortName=e.sortName),e.sortOrder&&(a.sortOrder=e.sortOrder),this.$store.dispatch("GetConnect",{url:"member/afterSaleService",data:a}).then(function(e){t.tableData=e.data.rows,t.total=e.data.total,t.tableLoading=!1}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},toAfterSaleDetails:function(e,t){this.isShow=!1;var a={id:e.member_id,type:t};this.$router.push({path:"/user/afterSales/details",query:a})}}},l=(a("2V0E"),a("KHd+")),s=Object(l.a)(n,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container  bg-gray"},[a("transition",{attrs:{name:"fade-transform",mode:"out-in"}},[a("router-view")],1),e._v(" "),e.isShow?a("el-row",{staticClass:"box-1"},[a("el-col",{attrs:{span:24}},[a("el-card",[a("div",{staticClass:"header"},[a("div",[a("el-select",{attrs:{placeholder:"客户星级",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.level,callback:function(t){e.$set(e.searchTerms,"level",t)},expression:"searchTerms.level"}},[a("el-option",{attrs:{value:1,label:"一星客户"}}),e._v(" "),a("el-option",{attrs:{value:2,label:"二星客户"}}),e._v(" "),a("el-option",{attrs:{value:3,label:"三星客户"}}),e._v(" "),a("el-option",{attrs:{value:4,label:"四星客户"}}),e._v(" "),a("el-option",{attrs:{value:5,label:"五星客户"}})],1),e._v(" "),a("el-select",{attrs:{placeholder:"搜索类型",clearable:""},model:{value:e.searchTerms.type,callback:function(t){e.$set(e.searchTerms,"type",t)},expression:"searchTerms.type"}},[a("el-option",{attrs:{value:1,label:"客户名称"}}),e._v(" "),a("el-option",{attrs:{value:2,label:"手机号"}}),e._v(" "),a("el-option",{attrs:{value:3,label:"跟进人"}}),e._v(" "),a("el-option",{attrs:{value:4,label:"ID"}})],1),e._v(" "),a("el-input",{staticClass:"search-input",attrs:{placeholder:"请输入内容"},on:{change:e.getData},model:{value:e.searchTerms.searchValue,callback:function(t){e.$set(e.searchTerms,"searchValue",t)},expression:"searchTerms.searchValue"}}),e._v(" "),a("el-button",{staticClass:"do-btn",attrs:{type:"success",icon:"el-icon-search"},on:{click:e.getData}},[e._v("搜索")])],1)]),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange}},[a("el-table-column",{attrs:{prop:"star_class",label:"客户星级",width:"160","header-align":"center",align:"center",sortable:""},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-rate",{attrs:{disabled:""},model:{value:t.row.star_class,callback:function(a){e.$set(t.row,"star_class",a)},expression:"scope.row['star_class']"}})]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"客户名称","min-width":"100","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(t.row.member_name?t.row.member_name:"--")+"\n            ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"member_phone",label:"手机号","min-width":"120","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"admin_name",label:"跟进人","min-width":"100","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"order_number",label:"最近订单","min-width":"120","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"surplus_day",label:"到期时间",width:"120","header-align":"center",align:"center",sortable:""},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(t.row.surplus_day+"天")+"\n            ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"提醒时间",width:"160","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(t.row.remind_time||"未设置")+"\n            ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"expire",label:"到期时间",width:"120","header-align":"center",align:"center",sortable:""},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(t.row.expire?t.row.expire+"天":"--")+"\n            ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"备注","min-width":"220","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(t.row.remarks||"--")+"\n            ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"320","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{type:"primary",size:"small"},on:{click:function(a){e.toAfterSaleDetails(t.row,1)}}},[e._v("沟通")]),e._v(" "),a("el-button",{attrs:{type:"info",size:"small"},on:{click:function(a){e.toAfterSaleDetails(t.row,2)}}},[e._v("运维")]),e._v(" "),a("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small"},on:{click:function(a){e.toAfterSaleDetails(t.row,3)}}},[e._v("合同")]),e._v(" "),a("el-button",{attrs:{type:"danger",size:"small"},on:{click:function(a){e.toAfterSaleDetails(t.row,4)}}},[e._v("订单")])]}}])})],1),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1)],1)],1):e._e()],1)},[],!1,null,"251a3523",null);s.options.__file="index.vue";t.default=s.exports},"zAZ/":function(e,t,a){"use strict";a.d(t,"e",function(){return l}),a.d(t,"c",function(){return s}),a.d(t,"d",function(){return o}),a.d(t,"a",function(){return i}),a.d(t,"f",function(){return c}),a.d(t,"b",function(){return u});var r=a("EJiy"),n=a.n(r);function l(e){var t=e.column,a=e.prop,r=e.order;this.order={column:t,prop:a,order:r},this.getData({sortName:a,sortOrder:r&&("ascending"===r?"asc":"desc")})}function s(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function o(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function i(e){this.th[e].check=!this.th[e].check}function c(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",r=void 0;"object"===(void 0===e?"undefined":n()(e))?r=e:(10===(""+e).length&&(e=1e3*parseInt(e)),r=new Date(e));var l={y:r.getFullYear(),m:r.getMonth()+1,d:r.getDate(),h:r.getHours(),i:r.getMinutes(),s:r.getSeconds(),a:r.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=l[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}function u(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var a=new Date(t),r=new Date;r=r.valueOf();var n=Math.ceil((a-r)/864e5);return 0===n?"今天":n>0&&n<30?n+"天":n>=30&&n<360?Math.ceil(n/30)+"月":n>=360?Math.ceil(n/360)+"年":"逾期"+Math.ceil((r-a)/864e5)+"天"}}}]);