(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-43a0"],{SrXh:function(e,t,a){"use strict";var n=a("ZLoL");a.n(n).a},ZLoL:function(e,t,a){},dheN:function(e,t,a){"use strict";a.r(t);var n={data:function(){return{dialogVisible:!1,limitEdit:!0,limit:10,temporaryEdit:!0,temporaryLimit:10,multipleSelection:[],currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,total:30,transfer:{id:"",where:0,toId:""},tableData:[{name:"朱峰",department:"网商",count:5,date:"19:00:00"},{name:"朱峰",department:"网商",count:5,date:"19:00:00"},{name:"朱峰",department:"网商",count:5,date:"19:00:00"},{name:"朱峰",department:"网商",count:5,date:"19:00:00"},{name:"朱峰",department:"网商",count:5,date:"19:00:00"},{name:"朱峰",department:"网商",count:5,date:"19:00:00"},{name:"朱峰",department:"网商",count:5,date:"19:00:00"}],tableData2:[{name:"朱峰",date:"19:00:00",contact_value:"今天天气不错,适合销售"},{name:"朱峰",date:"19:00:00",contact_value:"今天天气不错,适合销售"},{name:"朱峰",date:"19:00:00",contact_value:"今天天气不错,适合销售"},{name:"朱峰",date:"19:00:00",contact_value:"今天天气不错,适合销售"},{name:"朱峰",date:"19:00:00",contact_value:"今天天气不错,适合销售"},{name:"朱峰",date:"19:00:00",contact_value:"今天天气不错,适合销售"},{name:"朱峰",date:"19:00:00",contact_value:"今天天气不错,适合销售"}]}},watch:{},methods:{handleSizeChange:function(e){},handleCurrentChange:function(e){},toggleSelection:function(e){var t=this;e?e.forEach(function(e){t.$refs.multipleTable.toggleRowSelection(e)}):this.$refs.multipleTable.clearSelection()},handleSelectionChange:function(e){this.multipleSelection=e}}},i=(a("SrXh"),a("KHd+")),l=Object(i.a)(n,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-card",{staticClass:"box-1"},[a("div",{staticClass:"header"},[a("div",[a("span",[e._v("销售每日可添加客户数：")]),e._v(" "),a("el-input",{staticClass:"search-input",attrs:{disabled:e.limitEdit,size:"mini"},model:{value:e.limit,callback:function(t){e.limit=t},expression:"limit"}}),e._v(" "),a("el-button",{class:e.limitEdit?"":"bg-green",attrs:{icon:e.limitEdit?"el-icon-edit":"",type:e.limitEdit?"info":"success",size:"mini"},on:{click:function(t){e.limitEdit=!e.limitEdit}}},[e._v(e._s(e.limitEdit?"":"保存"))])],1),e._v(" "),a("div",[a("span",[e._v("临时调额额度：")]),e._v(" "),a("el-input",{staticClass:"search-input",attrs:{disabled:e.temporaryEdit,size:"mini"},model:{value:e.temporaryLimit,callback:function(t){e.temporaryLimit=t},expression:"temporaryLimit"}}),e._v(" "),a("el-button",{class:e.temporaryEdit?"":"bg-green",attrs:{icon:e.temporaryEdit?"el-icon-edit":"",type:e.temporaryEdit?"info":"success",size:"mini"},on:{click:function(t){e.temporaryEdit=!e.temporaryEdit}}},[e._v(e._s(e.temporaryEdit?"":"保存"))])],1),e._v(" "),a("div",[a("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-plus"}},[e._v("批量同意")]),e._v(" "),a("el-button",{attrs:{type:"danger",icon:"el-icon-delete"}},[e._v("批量拒绝")])],1)]),e._v(" "),a("el-table",{attrs:{data:e.tableData,border:""},on:{"selection-change":e.handleSelectionChange}},[a("el-table-column",{attrs:{type:"selection",width:"50","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"name",label:"姓名","min-width":"120","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"department",label:"部门","min-width":"100","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"count",label:"已申请次数","min-width":"100","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"date",label:"申请时间","min-width":"150","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{label:"客户沟通情况","min-width":"120","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{type:"primary",size:"small",icon:"el-icon-search",circle:""},on:{click:function(t){e.dialogVisible=!0}}})]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作","min-width":"200","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small",icon:"el-icon-check",circle:""}}),e._v(" "),a("el-button",{attrs:{type:"danger",size:"small",icon:"el-icon-close",circle:""}})]}}])})],1)],1),e._v(" "),a("el-dialog",{staticClass:"dialog",attrs:{visible:e.dialogVisible,title:"已添加客户沟通情况",width:"400",center:""},on:{"update:visible":function(t){e.dialogVisible=t}}},[a("div",{staticClass:"content"},[a("el-table",{attrs:{data:e.tableData2,border:""},on:{"selection-change":e.handleSelectionChange}},[a("el-table-column",{attrs:{type:"selection",width:"50","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"name",label:"客户昵称","min-width":"100","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"date",label:"最近联系时间","min-width":"150","header-align":"center",align:"center",sortable:""}}),e._v(" "),a("el-table-column",{attrs:{prop:"contact_value",label:"沟通内容","min-width":"250","header-align":"center",align:"center","show-overflow-tooltip":""}})],1),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,total:e.total,layout:"total, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange}})],1)])],1)},[],!1,null,"6102d524",null);l.options.__file="limit.vue";t.default=l.exports}}]);