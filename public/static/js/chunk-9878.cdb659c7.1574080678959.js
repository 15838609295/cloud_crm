(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-9878"],{Ff9r:function(e,t,a){},KEf6:function(e,t,a){"use strict";var r=a("Ff9r");a.n(r).a},j6Rf:function(e,t,a){"use strict";a.r(t);var r=a("zAZ/"),n={data:function(){return{searchTerms:{areaId:"",status:"",searchValue:"",date:[]},areaList:[],downloadLoading:!1,tableMaxHeight:document.documentElement.clientHeight-280,tableLoading:!1,currentPage:1,pageSize:10,pageSizes:[5,10,20,50,100,1e3],total:0,tableData:[]}},watch:{},created:function(){this.getData(),this.getAreaList()},methods:{handleCurrentChange:r.c,handleSizeChange:r.d,handleSortChange:r.e,parseTime:r.f,getData:function(e){var t=this;this.tableLoading=!0,e||(e={});var a={pageNo:this.currentPage,pageSize:this.pageSize,searchKey:this.searchTerms.searchValue,street_id:this.searchTerms.areaId,status:this.searchTerms.status};this.searchTerms.date&&0!==this.searchTerms.date.length&&(a.startTime=this.searchTerms.date[0]+" 00:00:00",a.endTime=this.searchTerms.date[1]+" 23:59:59"),e.sortName&&(a.sortName=e.sortName),e.sortOrder&&(a.sortOrder=e.sortOrder),this.$store.dispatch("GetConnect",{url:"workorder/changeOrderList",data:a}).then(function(e){t.tableData=e.data.rows,t.total=e.data.total,t.tableLoading=!1}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},getAreaList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"workorder/getNoPageStreet"}).then(function(t){e.areaList=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},toDetails:function(e){this.$router.push({path:"/workOrder/transferInfo",query:{id:e.id}})},toHandle:function(e){this.$router.push({path:"/workOrder/transferHandle",query:{id:e.id}})},toDelete:function(e){var t=this;this.$confirm("此操作将删除该工单, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var a="workorder/delWorkOrder/"+e.id;t.$store.dispatch("GetConnect",{url:a}).then(function(e){t.$message.success(e.msg),t.getData()}).catch(function(e){t.$message.error(e.msg)})})}}},s=(a("KEf6"),a("KHd+")),i=Object(s.a)(n,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-row",{staticClass:"box-1"},[a("el-col",{attrs:{span:24}},[a("el-card",[a("div",{staticClass:"header"},[a("div",[a("el-select",{staticClass:"select",attrs:{placeholder:"所属区域",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.areaId,callback:function(t){e.$set(e.searchTerms,"areaId",t)},expression:"searchTerms.areaId"}},e._l(e.areaList,function(e){return a("el-option",{key:e.id,attrs:{label:e.name,value:e.id}})})),e._v(" "),a("el-select",{staticClass:"select",attrs:{placeholder:"转单状态",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.status,callback:function(t){e.$set(e.searchTerms,"status",t)},expression:"searchTerms.status"}},[a("el-option",{attrs:{value:0,label:"待处理"}}),e._v(" "),a("el-option",{attrs:{value:1,label:"已转单"}}),e._v(" "),a("el-option",{attrs:{value:2,label:"已驳回"}})],1),e._v(" "),a("el-date-picker",{staticClass:"picker",attrs:{type:"daterange","value-format":"yyyy-MM-dd","range-separator":"-","start-placeholder":"开始日期","end-placeholder":"结束日期"},on:{change:e.getData},model:{value:e.searchTerms.date,callback:function(t){e.$set(e.searchTerms,"date",t)},expression:"searchTerms.date"}}),e._v(" "),a("el-input",{staticClass:"search-input",attrs:{placeholder:"请输入姓名或手机号码"},on:{change:e.getData},model:{value:e.searchTerms.searchValue,callback:function(t){e.$set(e.searchTerms,"searchValue",t)},expression:"searchTerms.searchValue"}}),e._v(" "),a("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-search"},on:{click:e.getData}},[e._v("搜索")])],1)]),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange}},[a("el-table-column",{attrs:{prop:"id",label:"ID",width:"65","header-align":"center",align:"center",sortable:""}}),e._v(" "),a("el-table-column",{attrs:{prop:"old_admin_name",label:"转单网格员","min-width":"120","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"old_admin_mobile",label:"联系方式","min-width":"160","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"street_name",label:"所属区域","min-width":"120","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"type_name",label:"问题类别","min-width":"120","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-tag",{attrs:{type:"info"}},[e._v(e._s(t.row.type_name))])]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"change_remarks",label:"网格员备注","min-width":"300","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"created_at",label:"提交时间","min-width":"160","header-align":"center",align:"center",sortable:""}}),e._v(" "),a("el-table-column",{attrs:{prop:"verify_time",label:"处理时间","min-width":"160","header-align":"center",align:"center",sortable:""},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(t.row.verify_time||"--")+"\n            ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"now_admin_name",label:"接单网格员","min-width":"120","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(t.row.now_admin_name||"--")+"\n            ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"status",label:"转单状态","min-width":"120","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[1===t.row.status?a("el-tag",{attrs:{type:"danger"}},[e._v("待处理")]):2===t.row.status?a("el-tag",{attrs:{type:"warning"}},[e._v("已驳回")]):4===t.row.status?a("el-tag",{attrs:{type:"warning"}},[e._v("已解决")]):a("el-tag",{attrs:{type:"success"}},[e._v("已转单")])]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"200","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{type:"primary",size:"small",icon:"el-icon-search"},on:{click:function(a){e.toDetails(t.row)}}},[e._v("详情")]),e._v(" "),1===t.row.status&&3!==t.row.work_order_status?a("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small",icon:"el-icon-s-check"},on:{click:function(a){e.toHandle(t.row)}}},[e._v("处理")]):e._e()]}}])})],1),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1)],1)],1)],1)},[],!1,null,"7a30bc85",null);i.options.__file="list.vue";t.default=i.exports},"zAZ/":function(e,t,a){"use strict";a.d(t,"e",function(){return s}),a.d(t,"c",function(){return i}),a.d(t,"d",function(){return l}),a.d(t,"a",function(){return o}),a.d(t,"f",function(){return c}),a.d(t,"b",function(){return d});var r=a("EJiy"),n=a.n(r);function s(e){var t=e.column,a=e.prop,r=e.order;this.order={column:t,prop:a,order:r},this.getData({sortName:a,sortOrder:r&&("ascending"===r?"asc":"desc")})}function i(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function l(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function o(e){this.th[e].check=!this.th[e].check}function c(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",r=void 0;"object"===(void 0===e?"undefined":n()(e))?r=e:(10===(""+e).length&&(e=1e3*parseInt(e)),r=new Date(e));var s={y:r.getFullYear(),m:r.getMonth()+1,d:r.getDate(),h:r.getHours(),i:r.getMinutes(),s:r.getSeconds(),a:r.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=s[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}function d(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var a=new Date(t),r=new Date;r=r.valueOf();var n=Math.ceil((a-r)/864e5);return 0===n?"今天":n>0&&n<30?n+"天":n>=30&&n<360?Math.ceil(n/30)+"月":n>=360?Math.ceil(n/360)+"年":"逾期"+Math.ceil((r-a)/864e5)+"天"}}}]);