(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-17f2"],{Yfch:function(t,e,a){"use strict";function r(t){return/^(https?|ftp):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/.test(t)}function s(t){return/^[A-Za-z0-9\u4e00-\u9fa5]+$/.test(t)&&!/^\d+$/.test(t)}function i(t){return/^[A-Za-z0-9\u4e00-\u9fa5]+$/.test(t)}function n(t,e){for(var a=t.split(""),r=0;r<a.length;r++)r>e-1&&r<a.length-e&&(a[r]="*");return a.join("")}a.d(e,"d",function(){return r}),a.d(e,"c",function(){return s}),a.d(e,"b",function(){return i}),a.d(e,"a",function(){return n})},ZYDS:function(t,e,a){},v0X0:function(t,e,a){"use strict";var r=a("ZYDS");a.n(r).a},wayM:function(t,e,a){"use strict";a.r(e);var r=a("zAZ/"),s=a("Yfch"),i={data:function(){return{isShow:!1,searchTerms:{searchValue:""},dialogVisible:!1,form:{id:"",name:"",status:0,sort:""},rules:{name:[{required:!0,message:"请输入标题",trigger:"change"}],sort:[{required:!0,message:"请输入权重",trigger:"change"}],status:[{required:!0,message:"请选择状态",trigger:"change"}]},tableMaxHeight:document.documentElement.clientHeight-280,tableLoading:!1,currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,pageSizes:[10,20,50,100,1e3],total:0,tableData:[]}},watch:{$route:function(t){var e=this;"信息类型"===t.name?(this.getData(),setTimeout(function(){e.isShow=!0},500)):this.isShow=!1}},created:function(){"信息类型"===this.$route.name&&(this.isShow=!0,this.getData())},methods:{handleCurrentChange:r.c,handleSizeChange:r.d,handleSortChange:r.e,getData:function(t){var e=this;this.tableLoading=!0,t||(t={});var a={page_no:this.currentPage,page_size:this.pageSize,search:this.searchTerms.searchValue,type:2};t.sortName&&(a.sortName=t.sortName),t.sortOrder&&(a.sortOrder=t.sortOrder),this.$store.dispatch("GetConnect",{url:"lookarticles/typeList",data:a}).then(function(t){e.tableData=t.data.rows,e.total=t.data.total,e.tableLoading=!1}).catch(function(t){e.tableLoading=!1,e.$message.error(t.msg+",请刷新或联系管理员")})},toNewsList:function(t){this.isShow=!1,this.$router.push({path:"/information/newslist/list",query:{id:t.id}})},toCreateNews:function(){this.isShow=!1;this.$router.push({path:"/information/newslist/modify",query:{title:"发布信息"}})},setState:function(t){var e=this;t.status=0===t.status?1:0;var a={id:t.id,is_display:t.status};this.$store.dispatch("GetConnect",{url:"lookarticles/updateStatus",data:a}).then(function(a){e.$message.success("修改信息类型状态成功，状态："+(1===t.status?"已显示":"已隐藏"))}).catch(function(a){t.status=0===t.status?1:0,e.$message.error("修改信息类型状态失败，请刷新或联系管理员")})},toDelete:function(t){var e=this;this.$confirm("此操作将把该信息类型删除, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var a="lookarticles/delType/"+t.id;e.$store.dispatch("GetConnect",{url:a}).then(function(t){e.$message.success(t.msg),e.getData()}).catch(function(t){e.$message.error("删除失败,请刷新或联系管理员!")})})},toAdd:function(){this.form={},this.dialogVisible=!0,this.form.title="添加类型",this.form.sort="",this.form.name="",this.form.type=2},toEdit:function(t){this.form={},this.dialogVisible=!0,this.form={id:t.id,name:t.name,status:t.status,sort:t.sort},this.form.title="编辑类型"},submitDialog:function(t){var e=this;this.$refs[t].validate(function(t){if(t){var a=e.form.id?"/lookarticles/updateType":"/lookarticles/addType",r=e.form;if(!Object(s.b)(e.form.name))return void e.$message.error("信息类型不能包含特殊字符");e.$store.dispatch("GetConnect",{url:a,data:r}).then(function(t){e.dialogVisible=!1,e.$message.success(t.msg),e.getData()}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员!")})}else e.$message.error("提交失败,请检查必填项")})},resetDialog:function(t){this.$refs[t].resetFields()}}},n=(a("v0X0"),a("KHd+")),o=Object(n.a)(i,function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"app-container  bg-gray"},[a("transition",{attrs:{name:"fade-transform",mode:"out-in"}},[a("router-view")],1),t._v(" "),t.isShow?a("el-row",{staticClass:"box-1"},[a("el-col",{attrs:{span:24}},[a("el-card",[a("div",{staticClass:"header"},[a("div",[t.queryMatch(46)?a("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-circle-plus"},on:{click:t.toCreateNews}},[t._v("发布信息")]):t._e(),t._v(" "),t.queryMatch(46)?a("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-circle-plus"},on:{click:t.toAdd}},[t._v("新增类型")]):t._e()],1)]),t._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.tableLoading,expression:"tableLoading"}],attrs:{data:t.tableData,"max-height":t.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":t.handleSortChange}},[a("el-table-column",{attrs:{prop:"id",label:"ID","min-width":"80","header-align":"center",align:"center",sortable:""}}),t._v(" "),a("el-table-column",{attrs:{prop:"name",label:"类型标题","min-width":"260"}}),t._v(" "),a("el-table-column",{attrs:{prop:"sort",label:"排序权重",width:"120","header-align":"center",align:"center",sortable:""}}),t._v(" "),t.queryMatch(47)?a("el-table-column",{attrs:{label:"状态","min-width":"80","header-align":"center",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("div",{on:{click:function(a){t.queryMatch(47)&&t.setState(e.row)}}},[a("el-tag",{attrs:{type:0===e.row.status?"danger":"success"}},[t._v(t._s(0===e.row.status?"已隐藏":"已显示"))])],1)]}}])}):t._e(),t._v(" "),t.queryMatch(47)?a("el-table-column",{attrs:{label:"操作","min-width":"200","header-align":"center",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("el-tooltip",{attrs:{content:"信息列表",placement:"top"}},[a("el-button",{attrs:{size:"small",type:"primary",icon:"el-icon-more",circle:""},on:{click:function(a){t.toNewsList(e.row)}}})],1),t._v(" "),t.queryMatch(47)?a("el-button",{staticClass:"bg-green",staticStyle:{"margin-left":"10px"},attrs:{type:"success",size:"small",icon:"el-icon-edit",circle:""},on:{click:function(a){t.toEdit(e.row)}}}):t._e(),t._v(" "),t.queryMatch(48)?a("el-button",{attrs:{type:"danger",size:"small",icon:"el-icon-delete",circle:""},on:{click:function(a){t.toDelete(e.row)}}}):t._e()]}}])}):t._e()],1),t._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":t.currentPage,"page-size":t.pageSize,"page-sizes":t.pageSizes,total:t.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(e){t.currentPage=e},"current-change":t.handleCurrentChange,"size-change":t.handleSizeChange}})],1)],1)],1):t._e(),t._v(" "),a("el-dialog",{staticClass:"dialog",attrs:{visible:t.dialogVisible,title:t.form.title,width:"500px",center:""},on:{"update:visible":function(e){t.dialogVisible=e},close:function(e){t.resetDialog("form")}}},[a("el-form",{ref:"form",attrs:{model:t.form,rules:t.rules,"label-width":"150px"}},[a("el-form-item",{attrs:{label:"类型名称",prop:"name"}},[a("el-input",{attrs:{autofocus:""},model:{value:t.form.name,callback:function(e){t.$set(t.form,"name",e)},expression:"form.name"}})],1),t._v(" "),a("el-form-item",{attrs:{label:"排序权重",prop:"sort"}},[a("el-input-number",{model:{value:t.form.sort,callback:function(e){t.$set(t.form,"sort",e)},expression:"form.sort"}}),t._v(" "),a("div",{staticClass:"remark"},[t._v("*注：权重越大排序越前")])],1),t._v(" "),a("el-form-item",{attrs:{label:"状态",prop:"status"}},[a("el-radio-group",{model:{value:t.form.status,callback:function(e){t.$set(t.form,"status",e)},expression:"form['status']"}},[a("el-radio",{attrs:{label:1}},[t._v("显示")]),t._v(" "),a("el-radio",{attrs:{label:0}},[t._v("隐藏")])],1)],1)],1),t._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(e){t.dialogVisible=!1}}},[t._v("取 消")]),t._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(e){t.submitDialog("form")}}},[t._v("确 定")])],1)],1)],1)},[],!1,null,"3685e738",null);o.options.__file="type.vue";e.default=o.exports},"zAZ/":function(t,e,a){"use strict";a.d(e,"e",function(){return i}),a.d(e,"c",function(){return n}),a.d(e,"d",function(){return o}),a.d(e,"a",function(){return c}),a.d(e,"f",function(){return l}),a.d(e,"b",function(){return u});var r=a("EJiy"),s=a.n(r);function i(t){var e=t.column,a=t.prop,r=t.order;this.order={column:e,prop:a,order:r},this.getData({sortName:a,sortOrder:r&&("ascending"===r?"asc":"desc")})}function n(t){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function o(t){this.$store.getters.userInfo.pageSize=t,this.pageSize=t,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function c(t){this.th[t].check=!this.th[t].check}function l(t,e){if(0===arguments.length)return null;var a=e||"{y}-{m}-{d} {h}:{i}:{s}",r=void 0;"object"===(void 0===t?"undefined":s()(t))?r=t:(10===(""+t).length&&(t=1e3*parseInt(t)),r=new Date(t));var i={y:r.getFullYear(),m:r.getMonth()+1,d:r.getDate(),h:r.getHours(),i:r.getMinutes(),s:r.getSeconds(),a:r.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(t,e){var a=i[e];return"a"===e?["日","一","二","三","四","五","六"][a]:(t.length>0&&a<10&&(a="0"+a),a||0)})}function u(t){var e=t.substr(0,10);e=e.replace(/-/g,"/");var a=new Date(e),r=new Date;r=r.valueOf();var s=Math.ceil((a-r)/864e5);return 0===s?"今天":s>0&&s<30?s+"天":s>=30&&s<360?Math.ceil(s/30)+"月":s>=360?Math.ceil(s/360)+"年":"逾期"+Math.ceil((r-a)/864e5)+"天"}}}]);