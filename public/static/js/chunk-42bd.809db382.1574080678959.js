(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-42bd"],{Y0cc:function(e,t,a){},dGhr:function(e,t,a){"use strict";a.r(t);var r=a("zAZ/"),s={data:function(){return{searchTerms:{searchValue:""},areaId:"",adminList:[],dialogVisible:!1,form:{id:"",name:"",mobile:"",user_name:"",status:1},rules:{name:[{required:!0,message:"请输入标题",trigger:"blur"}],user_name:[{required:!0,message:"请输入工作人员",trigger:"blur"}],mobile:[{required:!0,message:"请输入联系方式",trigger:"blur"}]},tableMaxHeight:document.documentElement.clientHeight-280,tableLoading:!1,currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,pageSizes:[10,20,50,100,1e3],total:0,tableData:[]}},created:function(){this.areaId=this.$route.query.id,this.getData()},methods:{handleCurrentChange:r.c,handleSizeChange:r.d,handleSortChange:r.e,getData:function(e){var t=this;this.tableLoading=!0,e||(e={});var a={pageNo:this.currentPage,pageSize:this.pageSize,search:this.searchTerms.searchValue};e.sortName&&(a.sortName=e.sortName),e.sortOrder&&(a.sortOrder=e.sortOrder),this.$store.dispatch("GetConnect",{url:"hotline/list",data:a}).then(function(e){t.tableData=e.data.rows,t.total=e.data.total,t.tableLoading=!1}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},getUsersList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"user/admin-list"}).then(function(t){e.adminList=t.data.list[0].personnel}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},toAdd:function(){this.form={},this.dialogVisible=!0,this.form.title="添加服务热线",this.form.status=1},toEdit:function(e){this.form={},this.dialogVisible=!0,this.form={id:e.id,name:e.name,mobile:e.mobile,user_name:e.user_name,status:e.status},this.form.title="编辑服务热线"},submitDialog:function(e){var t=this;this.$refs[e].validate(function(e){if(e){var a=t.form.id?"hotline/updateHotline":"hotline/addHotline",r=t.form;t.$store.dispatch("GetConnect",{url:a,data:r}).then(function(e){t.dialogVisible=!1,t.$message.success(e.msg),t.getData()}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员!")})}else t.$message.error("提交失败,请检查必填项")})},resetDialog:function(e){this.$refs[e].resetFields()},toDelete:function(e){var t=this;this.$confirm("此操作将删除该服务热线, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var a={id:e.id};t.$store.dispatch("GetConnect",{url:"hotline/delHotline",data:a}).then(function(e){t.$message.success(e.msg),t.getData()}).catch(function(e){t.$message.error(e.msg)})})},setState:function(e){var t=this;e.status=0===e.status?1:0;var a={id:e.id,status:e.status};this.$store.dispatch("GetConnect",{url:"hotline/updateHotlineStatus",data:a}).then(function(a){t.$message.success("修改服务热线状态成功，状态："+(1===e.status?"已启用":"已禁用")),t.getData()}).catch(function(a){e.status=0===e.status?1:0,t.$message.error(a.msg+"，请刷新或联系管理员")})}}},n=(a("qN6y"),a("KHd+")),i=Object(n.a)(s,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-row",{staticClass:"box-1"},[a("el-col",{attrs:{span:24}},[a("el-card",[a("div",{staticClass:"header"},[a("div",[a("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-circle-plus"},on:{click:e.toAdd}},[e._v("新增服务热线")]),e._v(" "),a("div",{staticClass:"flex-1"}),e._v(" "),a("el-input",{staticClass:"search-input",attrs:{placeholder:"请输入内容"},on:{change:e.getData},model:{value:e.searchTerms.searchValue,callback:function(t){e.$set(e.searchTerms,"searchValue",t)},expression:"searchTerms.searchValue"}}),e._v(" "),a("el-button",{staticClass:"do-btn",attrs:{type:"success",icon:"el-icon-search"},on:{click:e.getData}},[e._v("搜索")])],1)]),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange}},[a("el-table-column",{attrs:{prop:"id",label:"ID",width:"90","header-align":"center",align:"center",sortable:""}}),e._v(" "),a("el-table-column",{attrs:{prop:"name",label:"服务范围","min-width":"260"}}),e._v(" "),a("el-table-column",{attrs:{prop:"user_name",label:"工作人员",width:"160","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"mobile",label:"联系热线",width:"200","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(t.row.mobile||"--")+"\n            ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"created_at",label:"创建时间","min-width":"160"}}),e._v(" "),a("el-table-column",{attrs:{prop:"mobile",label:"状态",width:"140","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",{on:{click:function(a){e.setState(t.row)}}},[1===t.row.status?a("el-tag",{attrs:{type:"success"}},[e._v("已开启")]):a("el-tag",{attrs:{type:"danger"}},[e._v("已禁用")])],1)]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"200","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small",icon:"el-icon-edit",circle:""},on:{click:function(a){e.toEdit(t.row)}}}),e._v(" "),a("el-button",{attrs:{type:"danger",size:"small",icon:"el-icon-delete",circle:""},on:{click:function(a){e.toDelete(t.row)}}})]}}])})],1),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1)],1)],1),e._v(" "),a("el-dialog",{staticClass:"dialog",attrs:{visible:e.dialogVisible,title:e.form.title,width:"500px",center:""},on:{"update:visible":function(t){e.dialogVisible=t},close:function(t){e.resetDialog("form")}}},[a("el-form",{ref:"form",attrs:{model:e.form,rules:e.rules,"label-width":"120px"}},[a("el-form-item",{attrs:{label:"服务范围",prop:"name"}},[a("el-input",{attrs:{autofocus:""},model:{value:e.form.name,callback:function(t){e.$set(e.form,"name",t)},expression:"form.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"工作人员",prop:"user_name"}},[a("el-input",{model:{value:e.form.user_name,callback:function(t){e.$set(e.form,"user_name",t)},expression:"form.user_name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"联系热线",prop:"mobile"}},[a("el-input",{model:{value:e.form.mobile,callback:function(t){e.$set(e.form,"mobile",t)},expression:"form.mobile"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"状态",prop:"mobile"}},[a("el-radio-group",{attrs:{size:"small"},model:{value:e.form.status,callback:function(t){e.$set(e.form,"status",t)},expression:"form.status"}},[a("el-radio-button",{attrs:{label:1}},[e._v("开启")]),e._v(" "),a("el-radio-button",{attrs:{label:0}},[e._v("关闭")])],1)],1)],1),e._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.dialogVisible=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.submitDialog("form")}}},[e._v("确 定")])],1)],1)],1)},[],!1,null,"5b5bd436",null);i.options.__file="index.vue";t.default=i.exports},qN6y:function(e,t,a){"use strict";var r=a("Y0cc");a.n(r).a},"zAZ/":function(e,t,a){"use strict";a.d(t,"e",function(){return n}),a.d(t,"c",function(){return i}),a.d(t,"d",function(){return o}),a.d(t,"a",function(){return l}),a.d(t,"f",function(){return c}),a.d(t,"b",function(){return u});var r=a("EJiy"),s=a.n(r);function n(e){var t=e.column,a=e.prop,r=e.order;this.order={column:t,prop:a,order:r},this.getData({sortName:a,sortOrder:r&&("ascending"===r?"asc":"desc")})}function i(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function o(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function l(e){this.th[e].check=!this.th[e].check}function c(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",r=void 0;"object"===(void 0===e?"undefined":s()(e))?r=e:(10===(""+e).length&&(e=1e3*parseInt(e)),r=new Date(e));var n={y:r.getFullYear(),m:r.getMonth()+1,d:r.getDate(),h:r.getHours(),i:r.getMinutes(),s:r.getSeconds(),a:r.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=n[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}function u(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var a=new Date(t),r=new Date;r=r.valueOf();var s=Math.ceil((a-r)/864e5);return 0===s?"今天":s>0&&s<30?s+"天":s>=30&&s<360?Math.ceil(s/30)+"月":s>=360?Math.ceil(s/360)+"年":"逾期"+Math.ceil((r-a)/864e5)+"天"}}}]);