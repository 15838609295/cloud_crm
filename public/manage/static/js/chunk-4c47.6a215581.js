(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-4c47"],{C61S:function(e,t,a){"use strict";a.r(t);var i=a("14Xm"),r=a.n(i),n=a("D3Ub"),s=a.n(n),o=a("zAZ/"),l={data:function(){return{cid:"",tableMaxHeight:document.documentElement.clientHeight-280,tableLoading:!1,searchValue:"",currentPage:1,pageSize:20,pageSizes:[10,20,50,100,1e3],total:0,tableData:[],dialogVisible:!1,form:{title:"",label:"",name:"",icon:"",description:"",cid:0},rules:{label:[{required:!0,message:"请输入权限名称",trigger:"change"}],name:[{required:!0,message:"请输入权限规则",trigger:"change"}],description:[{required:!0,message:"请输入权限概述",trigger:"change"}]}}},watch:{},created:function(){this.cid=this.$route.query.cid,this.loadData()},methods:{handleSizeChange:o.b,handleCurrentChange:o.a,loadData:function(e){var t=this;s()(r.a.mark(function e(){var a,i,n;return r.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,t.$service.adminGetPermissionsList({page:t.currentPage,pageSize:t.pageSize,cid:t.cid,search:t.searchValue});case 2:if(0===(a=e.sent).code){for(i=a.data.rows||[],n=0;n<i.length;n++)i[n].display=!i[n].display;t.tableData=i,t.total=a.data.total}else t.$message.error(a.msg);case 4:case"end":return e.stop()}},e,t)}))()},submitDialog:function(e){var t=this;this.$refs[e].validate(function(e){e&&s()(r.a.mark(function e(){var a;return r.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:if(a=null,!t.form.id){e.next=7;break}return e.next=4,t.$service.adminUpdatePermissions({id:t.form.id,name:t.form.name,label:t.form.label,description:t.form.description,cid:t.cid,display:!0===t.form.display?0:1});case 4:a=e.sent,e.next=10;break;case 7:return e.next=9,t.$service.adminAddPermissions({name:t.form.name,label:t.form.label,description:t.form.description,cid:t.cid,display:1});case 9:a=e.sent;case 10:0===a.code?t.loadData():t.$message.error(a.msg),t.dialogVisible=!1;case 12:case"end":return e.stop()}},e,t)}))()})},resetDialog:function(e){this.$refs[e].resetFields()},toAdd:function(){this.form={},this.dialogVisible=!0,this.title="添加权限"},toEdit:function(e){this.form={},this.dialogVisible=!0,this.form={id:e.id,label:e.label,name:e.name,description:e.description,display:e.display},this.form.title="编辑权限"},toDelete:function(e){var t=this;this.$confirm("此操作将删除该功能, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){s()(r.a.mark(function a(){var i;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return a.next=2,t.$service.adminDeletePermissions({id:e.id});case 2:0===(i=a.sent).code?(t.$message.success(i.msg),t.loadData()):t.$message.error(i.msg);case 4:case"end":return a.stop()}},a,t)}))()})},toChangeStatus:function(e){var t=this;s()(r.a.mark(function a(){var i;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return a.next=2,t.$service.adminUpdatePermissionsStatus({id:e.id,status:!0===e.display?1:2});case 2:0===(i=a.sent).code?t.$message.success(i.msg):t.$message.error(i.msg);case 4:case"end":return a.stop()}},a,t)}))()}}},c=(a("Qoje"),a("KHd+")),d=Object(c.a)(l,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-row",{staticClass:"box-1"},[a("el-col",{attrs:{span:24}},[a("el-card",[a("div",{staticClass:"header"},[a("div",[a("el-input",{staticClass:"search-input",attrs:{placeholder:"请输入内容"},on:{change:e.loadData},model:{value:e.searchValue,callback:function(t){e.searchValue=t},expression:"searchValue"}}),e._v(" "),a("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-search"},on:{click:e.loadData}},[e._v("搜索")])],1),e._v(" "),a("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-circle-plus"},on:{click:e.toAdd}},[e._v("添加权限")])],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""}},[a("el-table-column",{attrs:{prop:"id",label:"ID","min-width":"65","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"name",label:"权限规则","min-width":"160","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"label",label:"权限名称","min-width":"140","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"description",label:"权限概述","min-width":"140","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(t.row.description?t.row.description:"--")+"\n            ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"description",label:"是否打开","min-width":"140","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-switch",{on:{change:function(a){e.toChangeStatus(t.row)}},model:{value:t.row.display,callback:function(a){e.$set(t.row,"display",a)},expression:"scope.row.display"}})]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作","min-width":"160","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small",icon:"el-icon-edit",circle:""},on:{click:function(a){e.toEdit(t.row)}}}),e._v(" "),a("el-button",{attrs:{type:"danger",size:"small",icon:"el-icon-delete",circle:""},on:{click:function(a){e.toDelete(t.row)}}})]}}])})],1),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1)],1)],1),e._v(" "),a("el-dialog",{staticClass:"dialog",attrs:{visible:e.dialogVisible,title:e.form.title,width:"520px",center:""},on:{"update:visible":function(t){e.dialogVisible=t},close:function(t){e.resetDialog("form")}}},[a("el-form",{ref:"form",attrs:{model:e.form,rules:e.rules,"label-width":"120px"}},[a("el-form-item",{attrs:{label:"权限规则",prop:"name"}},[a("el-input",{attrs:{autofocus:""},model:{value:e.form.name,callback:function(t){e.$set(e.form,"name",t)},expression:"form.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"权限名称",prop:"label"}},[a("el-input",{model:{value:e.form.label,callback:function(t){e.$set(e.form,"label",t)},expression:"form.label"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"权限概述",prop:"description"}},[a("el-input",{model:{value:e.form.description,callback:function(t){e.$set(e.form,"description",t)},expression:"form.description"}})],1)],1),e._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.dialogVisible=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.submitDialog("form")}}},[e._v("确 定")])],1)],1)],1)},[],!1,null,"4806a8b4",null);d.options.__file="subitems.vue";t.default=d.exports},EaKf:function(e,t,a){},Qoje:function(e,t,a){"use strict";var i=a("EaKf");a.n(i).a},"zAZ/":function(e,t,a){"use strict";a.d(t,"a",function(){return i}),a.d(t,"b",function(){return r});a("EJiy");function i(e){this.loadData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function r(e){this.loadData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}}}]);