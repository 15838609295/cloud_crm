(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-4615"],{GoYT:function(e,t,r){"use strict";r.r(t);var a=r("zAZ/"),n=r("Yfch"),i={data:function(){return{searchTerms:{searchValue:""},dialogVisible:!1,form:{id:"",label:""},rules:{label:[{required:!0,message:"请输入标题",trigger:"change"}]},tableMaxHeight:document.documentElement.clientHeight-280,tableLoading:!1,currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,pageSizes:[10,20,50,100,1e3],total:0,tableData:[]}},created:function(){this.getData()},methods:{handleCurrentChange:a.c,handleSizeChange:a.d,handleSortChange:a.e,getData:function(e){var t=this;this.tableLoading=!0,e||(e={});var r={pageNo:this.currentPage,pageSize:this.pageSize,search:this.searchTerms.searchValue};e.sortName&&(r.sortName=e.sortName),e.sortOrder&&(r.sortOrder=e.sortOrder),this.$store.dispatch("GetConnect",{url:"workorder/feedbackTypeList",data:r}).then(function(e){t.tableData=e.data.rows,t.total=e.data.total,t.tableLoading=!1}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},toDelete:function(e){var t=this;this.$confirm("此操作将删除该问题类别, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var r="workorder/delFeedback/"+e.id;t.$store.dispatch("GetConnect",{url:r}).then(function(e){t.$message.success(e.msg),t.getData()}).catch(function(e){t.$message.error(e.msg)})})},toAdd:function(){this.form={},this.dialogVisible=!0,this.form.title="添加问题类别"},toEdit:function(e){this.form={},this.dialogVisible=!0,this.form={id:e.id,label:e.label},this.form.title="编辑问题类别"},submitDialog:function(e){var t=this;this.$refs[e].validate(function(e){if(e){var r=t.form.id?"workorder/updateFeedback":"workorder/addFeedback",a=t.form;if(!Object(n.b)(t.form.name))return void t.$message.error("问题类型不能包含特殊字符");t.$store.dispatch("GetConnect",{url:r,data:a}).then(function(e){t.dialogVisible=!1,t.$message.success(e.msg),t.getData()}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员!")})}else t.$message.error("提交失败,请检查必填项")})},resetDialog:function(e){this.$refs[e].resetFields()}}},o=(r("VilE"),r("KHd+")),s=Object(o.a)(i,function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",{staticClass:"app-container  bg-gray"},[r("el-row",{staticClass:"box-1"},[r("el-col",{attrs:{span:24}},[r("el-card",[r("div",{staticClass:"header"},[r("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-circle-plus"},on:{click:e.toAdd}},[e._v("新增问题类别")])],1),e._v(" "),r("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange}},[r("el-table-column",{attrs:{prop:"id",label:"ID",width:"80","header-align":"center",align:"center",sortable:""}}),e._v(" "),r("el-table-column",{attrs:{prop:"label",label:"问题类别","min-width":"260"}}),e._v(" "),r("el-table-column",{attrs:{label:"操作",width:"200","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[r("el-button",{staticClass:"bg-green",staticStyle:{"margin-left":"10px"},attrs:{type:"success",size:"small",icon:"el-icon-edit"},on:{click:function(r){e.toEdit(t.row)}}},[e._v("编辑")]),e._v(" "),r("el-button",{attrs:{type:"danger",size:"small",icon:"el-icon-delete"},on:{click:function(r){e.toDelete(t.row)}}},[e._v("删除")])]}}])})],1),e._v(" "),r("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1)],1)],1),e._v(" "),r("el-dialog",{staticClass:"dialog",attrs:{visible:e.dialogVisible,title:e.form.title,width:"500px",center:""},on:{"update:visible":function(t){e.dialogVisible=t},close:function(t){e.resetDialog("form")}}},[r("el-form",{ref:"form",attrs:{model:e.form,rules:e.rules,"label-width":"120px"}},[r("el-form-item",{attrs:{label:"名称",prop:"label"}},[r("el-input",{attrs:{autofocus:""},model:{value:e.form.label,callback:function(t){e.$set(e.form,"label",t)},expression:"form.label"}})],1)],1),e._v(" "),r("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[r("el-button",{on:{click:function(t){e.dialogVisible=!1}}},[e._v("取 消")]),e._v(" "),r("el-button",{attrs:{type:"primary"},on:{click:function(t){e.submitDialog("form")}}},[e._v("确 定")])],1)],1)],1)},[],!1,null,"424a80c8",null);s.options.__file="type.vue";t.default=s.exports},VilE:function(e,t,r){"use strict";var a=r("w0B3");r.n(a).a},Yfch:function(e,t,r){"use strict";function a(e){return/^(https?|ftp):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/.test(e)}function n(e){return/^[A-Za-z0-9\u4e00-\u9fa5]+$/.test(e)&&!/^\d+$/.test(e)}function i(e){return/^[A-Za-z0-9\u4e00-\u9fa5]+$/.test(e)}function o(e,t){for(var r=e.split(""),a=0;a<r.length;a++)a>t-1&&a<r.length-t&&(r[a]="*");return r.join("")}r.d(t,"d",function(){return a}),r.d(t,"c",function(){return n}),r.d(t,"b",function(){return i}),r.d(t,"a",function(){return o})},w0B3:function(e,t,r){},"zAZ/":function(e,t,r){"use strict";r.d(t,"e",function(){return i}),r.d(t,"c",function(){return o}),r.d(t,"d",function(){return s}),r.d(t,"a",function(){return l}),r.d(t,"f",function(){return c}),r.d(t,"b",function(){return u});var a=r("EJiy"),n=r.n(a);function i(e){var t=e.column,r=e.prop,a=e.order;this.order={column:t,prop:r,order:a},this.getData({sortName:r,sortOrder:a&&("ascending"===a?"asc":"desc")})}function o(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function s(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function l(e){this.th[e].check=!this.th[e].check}function c(e,t){if(0===arguments.length)return null;var r=t||"{y}-{m}-{d} {h}:{i}:{s}",a=void 0;"object"===(void 0===e?"undefined":n()(e))?a=e:(10===(""+e).length&&(e=1e3*parseInt(e)),a=new Date(e));var i={y:a.getFullYear(),m:a.getMonth()+1,d:a.getDate(),h:a.getHours(),i:a.getMinutes(),s:a.getSeconds(),a:a.getDay()};return r.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var r=i[t];return"a"===t?["日","一","二","三","四","五","六"][r]:(e.length>0&&r<10&&(r="0"+r),r||0)})}function u(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var r=new Date(t),a=new Date;a=a.valueOf();var n=Math.ceil((r-a)/864e5);return 0===n?"今天":n>0&&n<30?n+"天":n>=30&&n<360?Math.ceil(n/30)+"月":n>=360?Math.ceil(n/360)+"年":"逾期"+Math.ceil((a-r)/864e5)+"天"}}}]);