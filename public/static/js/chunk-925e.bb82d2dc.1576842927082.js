(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-925e"],{EY4q:function(e,t,a){"use strict";var i=a("l6BQ");a.n(i).a},L1Ub:function(e,t,a){"use strict";a.r(t);var i=a("zAZ/"),n={data:function(){return{teamType:0,downloadLoading:!1,tableMaxHeight:document.documentElement.clientHeight-380,tableLoading:!1,tableData:[],dialogVisible:!1,form:{title:"",name:""},rules:{name:[{required:!0,message:"请输入分组名称",trigger:"change"}]}}},created:function(){this.teamType=Number(this.$route.query.type),this.getData()},methods:{handleCurrentChange:i.c,handleSizeChange:i.d,handleSortChange:i.e,parseTime:i.f,getData:function(e){var t=this;this.tableLoading=!0,e||(e={});this.$store.dispatch("GetConnect",{url:"exam/examineeGroupList"}).then(function(e){1===t.teamType?t.tableData=e.data.admin:t.tableData=e.data.member,t.tableLoading=!1}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},toAdd:function(){this.form={},this.dialogVisible=!0,this.form.title="添加分组"},toEdit:function(e){this.form={},this.dialogVisible=!0,this.form={id:e.id,name:e.name},this.form.title="编辑分组"},submitDialog:function(e){var t=this;this.$refs[e].validate(function(e){if(e){var a=t.form,i=a.id?"exam/updateExamineeGroup":"exam/addExamineeGroup";a.groupType=t.teamType,t.$store.dispatch("GetConnect",{url:i,data:a}).then(function(e){t.dialogVisible=!1,t.$message.success(e.msg),t.getData()}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员!")})}else t.$message.error("提交失败,请检查必填项")})},resetDialog:function(e){this.$refs[e].resetFields()},toDelete:function(e){var t=this;this.$confirm("此操作将删除该分组, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var a="/exam/delExamineeGroup/"+e.id;t.$store.dispatch("GetConnect",{url:a}).then(function(e){t.$message.success(e.msg),t.getData()}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员!")})})}}},r=(a("EY4q"),a("KHd+")),o=Object(r.a)(n,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-card",{staticClass:"box-1"},[a("div",{staticClass:"header"},[a("div",[a("el-button",{attrs:{type:"primary",icon:"el-icon-plus"},on:{click:e.toAdd}},[e._v("添加分组")])],1)]),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange}},[a("el-table-column",{attrs:{prop:"id",label:"ID",width:"120","header-align":"center",align:"center",sortable:""}}),e._v(" "),a("el-table-column",{attrs:{prop:"name",label:"分组名称","min-width":"160"}}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"200","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small",icon:"el-icon-edit",circle:""},on:{click:function(a){e.toEdit(t.row)}}}),e._v(" "),a("el-button",{attrs:{type:"danger",size:"small",icon:"el-icon-delete",circle:""},on:{click:function(a){e.toDelete(t.row)}}})]}}])})],1)],1),e._v(" "),a("el-dialog",{staticClass:"dialog",attrs:{visible:e.dialogVisible,title:e.form.title,width:"500px",center:""},on:{"update:visible":function(t){e.dialogVisible=t},close:function(t){e.resetDialog("form")}}},[a("el-form",{ref:"form",attrs:{model:e.form,rules:e.rules,"label-width":"120px"}},[a("el-form-item",{attrs:{label:"名称",prop:"name"}},[a("el-input",{attrs:{autofocus:""},model:{value:e.form.name,callback:function(t){e.$set(e.form,"name",t)},expression:"form.name"}})],1)],1),e._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.dialogVisible=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.submitDialog("form")}}},[e._v("确 定")])],1)],1)],1)},[],!1,null,"441fba47",null);o.options.__file="team.vue";t.default=o.exports},l6BQ:function(e,t,a){},"zAZ/":function(e,t,a){"use strict";a.d(t,"e",function(){return r}),a.d(t,"c",function(){return o}),a.d(t,"d",function(){return s}),a.d(t,"a",function(){return l}),a.d(t,"f",function(){return c}),a.d(t,"b",function(){return d});var i=a("EJiy"),n=a.n(i);function r(e){var t=e.column,a=e.prop,i=e.order;this.order={column:t,prop:a,order:i},this.getData({sortName:a,sortOrder:i&&("ascending"===i?"asc":"desc")})}function o(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function s(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function l(e){this.th[e].check=!this.th[e].check}function c(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",i=void 0;"object"===(void 0===e?"undefined":n()(e))?i=e:(10===(""+e).length&&(e=1e3*parseInt(e)),i=new Date(e));var r={y:i.getFullYear(),m:i.getMonth()+1,d:i.getDate(),h:i.getHours(),i:i.getMinutes(),s:i.getSeconds(),a:i.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=r[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}function d(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var a=new Date(t),i=new Date;i=i.valueOf();var n=Math.ceil((a-i)/864e5);return 0===n?"今天":n>0&&n<30?n+"天":n>=30&&n<360?Math.ceil(n/30)+"月":n>=360?Math.ceil(n/360)+"年":"逾期"+Math.ceil((i-a)/864e5)+"天"}}}]);