(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-01a4"],{"0Z1y":function(e,t,a){"use strict";var r=a("rWb/");a.n(r).a},"rWb/":function(e,t,a){},"x+1Q":function(e,t,a){"use strict";a.r(t);var r=a("zAZ/"),n={data:function(){return{searchTerms:{searchValue:""},tableMaxHeight:document.documentElement.clientHeight-280,tableLoading:!1,currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,pageSizes:[10,20,50,100,1e3],total:0,tableData:[],dialogVisible:!1,form:{title:"",name:"",wechat_channel_id:""},rules:{name:[{required:!0,message:"请输入角色名称",trigger:"change"}],wechat_channel_id:[{required:!0,message:"请输入企业微信部门ID",trigger:"change"}]}}},watch:{},created:function(){this.getData()},methods:{handleCurrentChange:r.c,handleSizeChange:r.d,handleSortChange:r.e,columnCheck:r.a,parseTime:r.f,getData:function(e){var t=this;this.tableLoading=!0,e||(e={});var a={page_no:this.currentPage,page_size:this.pageSize,search:this.searchTerms.searchValue};e.sortName&&(a.sortName=e.sortName),e.sortOrder&&(a.sortOrder=e.sortOrder),this.$store.dispatch("GetConnect",{url:"company/list",data:a}).then(function(e){t.tableData=e.data.rows,t.total=e.data.total,t.tableLoading=!1}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},toAdd:function(){this.form={},this.dialogVisible=!0,this.form.title="创建部门"},toEdit:function(e){this.form={},this.dialogVisible=!0,this.form={id:e.id,name:e.name,wechat_channel_id:e.wechat_channel_id},this.form.title="编辑部门"},submitDialog:function(e){var t=this;this.$refs[e].validate(function(e){if(e){var a=t.form.id?"/company/edit/"+t.form.id:"/company/create",r=t.form;t.$store.dispatch("GetConnect",{url:a,data:r}).then(function(e){t.dialogVisible=!1,t.$message.success(e.msg),t.getData()}).catch(function(e){t.$message.error(e.msg)})}else t.$message.error("提交失败,请检查必填项")})},resetDialog:function(e){this.$refs[e].resetFields()},toDelete:function(e){var t=this;this.$confirm("此操作将删除该部门, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var a="/company/delete/"+e.id;t.$store.dispatch("GetConnect",{url:a}).then(function(e){t.$message.success(e.msg),t.getData()}).catch(function(e){t.$message.error("删除失败,请刷新或联系管理员!")})})}}},i=(a("0Z1y"),a("KHd+")),s=Object(i.a)(n,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-row",{staticClass:"box-1"},[a("el-col",{attrs:{span:24}},[a("el-card",[a("div",{staticClass:"header"},[a("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-circle-plus"},on:{click:e.toAdd}},[e._v("创建部门")]),e._v(" "),a("div",[a("el-input",{staticClass:"search-input",attrs:{placeholder:"请输入内容"},on:{change:e.getData},model:{value:e.searchTerms.searchValue,callback:function(t){e.$set(e.searchTerms,"searchValue",t)},expression:"searchTerms.searchValue"}}),e._v(" "),a("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-search"},on:{click:e.getData}},[e._v("搜索")])],1)],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange}},[a("el-table-column",{attrs:{prop:"id",label:"ID",width:"100","header-align":"center",align:"center",sortable:""}}),e._v(" "),a("el-table-column",{attrs:{prop:"name",label:"部门名称","min-width":"220","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"wechat_channel_id",label:"企业微信部门ID","min-width":"160","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"created_at",label:"创建时间","min-width":"140","header-align":"center",align:"center",sortable:""}}),e._v(" "),a("el-table-column",{attrs:{prop:"updated_at",label:"更新时间","min-width":"140","header-align":"center",align:"center",sortable:""}}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"200","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small",icon:"el-icon-edit",circle:""},on:{click:function(a){e.toEdit(t.row)}}}),e._v(" "),a("el-button",{attrs:{type:"danger",size:"small",icon:"el-icon-delete",circle:""},on:{click:function(a){e.toDelete(t.row)}}})]}}])})],1),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1)],1)],1),e._v(" "),a("el-dialog",{staticClass:"dialog",attrs:{visible:e.dialogVisible,title:e.form.title,width:"500px",center:""},on:{"update:visible":function(t){e.dialogVisible=t},close:function(t){e.resetDialog("form")}}},[a("el-form",{ref:"form",attrs:{model:e.form,rules:e.rules,"label-width":"150px"}},[a("el-form-item",{attrs:{label:"部门名称",prop:"name"}},[a("el-input",{attrs:{autofocus:""},model:{value:e.form.name,callback:function(t){e.$set(e.form,"name",t)},expression:"form.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"企业微信部门ID",prop:"wechat_channel_id"}},[a("el-input",{model:{value:e.form.wechat_channel_id,callback:function(t){e.$set(e.form,"wechat_channel_id",t)},expression:"form.wechat_channel_id"}})],1)],1),e._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.dialogVisible=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.submitDialog("form")}}},[e._v("确 定")])],1)],1)],1)},[],!1,null,"d8ac5b8a",null);s.options.__file="department.vue";t.default=s.exports},"zAZ/":function(e,t,a){"use strict";a.d(t,"e",function(){return i}),a.d(t,"c",function(){return s}),a.d(t,"d",function(){return o}),a.d(t,"a",function(){return c}),a.d(t,"f",function(){return l}),a.d(t,"b",function(){return d});var r=a("EJiy"),n=a.n(r);function i(e){var t=e.column,a=e.prop,r=e.order;this.order={column:t,prop:a,order:r},this.getData({sortName:a,sortOrder:r&&("ascending"===r?"asc":"desc")})}function s(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function o(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function c(e){this.th[e].check=!this.th[e].check}function l(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",r=void 0;"object"===(void 0===e?"undefined":n()(e))?r=e:(10===(""+e).length&&(e=1e3*parseInt(e)),r=new Date(e));var i={y:r.getFullYear(),m:r.getMonth()+1,d:r.getDate(),h:r.getHours(),i:r.getMinutes(),s:r.getSeconds(),a:r.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=i[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}function d(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var a=new Date(t),r=new Date;r=r.valueOf();var n=Math.ceil((a-r)/864e5);return 0===n?"今天":n>0&&n<30?n+"天":n>=30&&n<360?Math.ceil(n/30)+"月":n>=360?Math.ceil(n/360)+"年":"逾期"+Math.ceil((r-a)/864e5)+"天"}}}]);