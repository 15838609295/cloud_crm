(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-df93"],{"1Wy2":function(e,t,a){"use strict";a.r(t);var n=a("zAZ/"),r={data:function(){return{searchTerms:{status:0,clientFlag:null,client_type:null,project_type:null,arrears:null,hasOverdueBill:null,searchValue:"",searchSelected:0},reviewDialogVisible:!1,dialogData:{ClientUin:"",AuditResult:"",Note:""},addAgentDialogVisible:!1,addAgentDialogData:{url:"https://partners.cloud.tencent.com/invitation/316656189255ac6a2b86868"},tableMaxHeight:document.documentElement.clientHeight-300,tableLoading:!1,currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,pageSizes:[10,20,50,100,500],total:0,tableData:[],examineTotal:0}},created:function(){this.getData({})},methods:{handleCurrentChange:n.c,handleSizeChange:n.d,handleSortChange:n.e,parseTime:n.f,getData:function(e){this.tableLoading=!0;var t={page_no:this.currentPage,page_size:this.pageSize,search:this.searchTerms.searchValue,client_flag:this.searchTerms.clientFlag,client_type:this.searchTerms.client_type,project_type:this.searchTerms.project_type,arrears:this.searchTerms.arrears};e.sortName&&(t.sortName=e.sortName),e.sortOrder&&(t.sortOrder=e.sortOrder),0===this.searchTerms.searchSelected?t.client_uin=this.searchTerms.searchValue:1===this.searchTerms.searchSelected?t.client_name=this.searchTerms.searchValue:t.client_remark=this.searchTerms.searchValue,0===this.searchTerms.status?this.getCustomerExamine(t):this.getCustomerBeAudited(t)},getCustomerBeAudited:function(e){var t=this;this.searchTerms.status=1,this.tableLoading=!0;this.$store.dispatch("GetConnect",{url:"tencent/customerBeAudited",data:e}).then(function(e){t.tableData=e.data.list,t.total=parseInt(e.data.total),t.examineTotal=parseInt(e.data.total),t.tableLoading=!1}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},getCustomerExamine:function(e){var t=this;this.searchTerms.status=0,this.tableLoading=!0;this.$store.dispatch("GetConnect",{url:"tencent/customerExamine",data:e}).then(function(a){t.tableData=a.data.list,t.total=parseInt(a.data.total),t.tableLoading=!1;t.$store.dispatch("GetConnect",{url:"tencent/customerBeAudited",data:e}).then(function(e){t.examineTotal=parseInt(e.data.total)}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},toReview:function(e){var t=this;this.dialogData.ClientUin=e.ClientUin,"b"===e.ClientFlag?this.reviewDialogVisible=!0:this.$confirm("是否审核通过该客户?","提示",{confirmButtonText:"同意",cancelButtonText:"拒绝",type:"warning"}).then(function(){t.doReview("accept")}).catch(function(){t.doReview("reject")})},doReview:function(e){var t=this,a=this.dialogData;a.AuditResult=e,this.$store.dispatch("GetConnect",{url:"tencent/examineCustomer",data:a}).then(function(a){t.$message.success("审核成功,状态"+e)}).catch(function(e){t.$message.error(e.msg+"，请刷新或联系管理员")})},downsQRCodeImage:function(){var e=document.createElement("a");e.target="_blank",e.href="https://crmweb.netbcloud.com/admin/download/file?token="+this.$store.getters.token+"&url=uploads/tencentQR/tencentQR.jpg",e.download="tencentQR",e.click()}}},l=(a("H750"),a("KHd+")),s=Object(l.a)(r,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-card",{staticClass:"box-1"},[a("div",{staticClass:"header"},[a("div",[a("el-button",{attrs:{type:"danger"},on:{click:function(t){e.addAgentDialogVisible=!0}}},[e._v("加代理")]),e._v(" "),a("el-button",{attrs:{type:0===e.searchTerms.status?"primary":""},on:{click:function(t){e.currentPage=1,e.getCustomerExamine()}}},[e._v("已审核")]),e._v(" "),a("el-button",{attrs:{type:1===e.searchTerms.status?"primary":""},on:{click:function(t){e.currentPage=1,e.getCustomerBeAudited()}}},[e._v("未审核（"+e._s(e.examineTotal)+"）")]),e._v(" "),a("el-select",{staticClass:"select",attrs:{placeholder:"客户类型",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.client_type,callback:function(t){e.$set(e.searchTerms,"client_type",t)},expression:"searchTerms['client_type']"}},[a("el-option",{attrs:{value:"new",label:"新拓"}}),e._v(" "),a("el-option",{attrs:{value:"assign",label:"指定"}}),e._v(" "),a("el-option",{attrs:{value:"old",label:"存量"}})],1),e._v(" "),a("el-select",{staticClass:"select",attrs:{placeholder:"项目类型",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.project_type,callback:function(t){e.$set(e.searchTerms,"project_type",t)},expression:"searchTerms['project_type']"}},[a("el-option",{attrs:{value:"self",label:"自拓项目"}}),e._v(" "),a("el-option",{attrs:{value:"platform",label:"合作项目"}}),e._v(" "),a("el-option",{attrs:{value:"repeat",label:"复算项目"}})],1),e._v(" "),a("el-select",{staticClass:"select",attrs:{placeholder:"是否欠费",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.arrears,callback:function(t){e.$set(e.searchTerms,"arrears",t)},expression:"searchTerms['arrears']"}},[a("el-option",{attrs:{value:1,label:"欠费"}}),e._v(" "),a("el-option",{attrs:{value:0,label:"不欠费"}})],1)],1),e._v(" "),a("div",[a("el-input",{staticClass:"input-with-select search-input",attrs:{placeholder:"请输入内容"},on:{change:e.getData},model:{value:e.searchTerms.searchValue,callback:function(t){e.$set(e.searchTerms,"searchValue",t)},expression:"searchTerms.searchValue"}},[a("el-select",{staticClass:"search-select",attrs:{slot:"prepend"},slot:"prepend",model:{value:e.searchTerms.searchSelected,callback:function(t){e.$set(e.searchTerms,"searchSelected",t)},expression:"searchTerms.searchSelected"}},[a("el-option",{attrs:{value:0,label:"客户账号ID"}}),e._v(" "),a("el-option",{attrs:{value:1,label:"客户名称"}}),e._v(" "),a("el-option",{attrs:{value:2,label:"客户备注"}})],1)],1),e._v(" "),a("el-button",{staticClass:"do-btn",attrs:{type:"success",icon:"el-icon-search"},on:{click:e.getData}},[e._v("搜索")])],1)]),e._v(" "),0===e.searchTerms.status?a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],key:"table1",attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange}},[a("el-table-column",{attrs:{prop:"ClientUin",label:"客户账号ID","min-width":"140","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.ClientUin)+"\n          "),a("div",{staticClass:"remarks"},[e._v(e._s(t.row.ClientRemark?t.row.ClientRemark:"--"))])]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"名称","min-width":"160","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.ClientName?t.row.ClientName:"--")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"ClientFlag",label:"客户类型","min-width":"120","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s("new"===t.row.ClientType?"新拓":"assign"===t.row.ClientType?"指定":"old"===t.row.ClientType?"存量":"--")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"项目类型","min-width":"120","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s("self"===t.row.ProjectType?"自拓项目":"platform"===t.row.ProjectType?"合作项目":"repeat"===t.row.ProjectType?"复算项目":"--")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"邮箱","min-width":"120","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.email?t.row.email:"--")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"是否欠费","min-width":"100","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.HasOverdueBill?"是":"否")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"LastMonthAmt",label:"上月消费","min-width":"120","header-align":"center",align:"center",sortable:""},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(null!==t.row.LastMonthAmt&&""!==t.row.LastMonthAmt&&t.row.LastMonthAmt?(t.row.LastMonthAmt/100).toFixed(2):"--")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"ThisMonthAmt",label:"本月消费","min-width":"120","header-align":"center",align:"center",sortable:""},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(null!==t.row.ThisMonthAmt&&""!==t.row.ThisMonthAmt&&t.row.ThisMonthAmt?(t.row.ThisMonthAmt/100).toFixed(2):"--")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"关联时间","min-width":"160","header-align":"center",align:"center",sortable:""},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.AgentTime?e.parseTime(t.row.AgentTime,"{y}-{m}-{d} {h}:{i}:{s}"):"--")+"\n        ")]}}])})],1):e._e(),e._v(" "),1===e.searchTerms.status?a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],key:"table2",attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange}},[a("el-table-column",{attrs:{prop:"ClientUin",label:"客户账号ID","min-width":"140","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{label:"客户类型","min-width":"120","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.ClientFlag)+"类\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"是否欠费","min-width":"100","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.HasOverdueBill?"是":"否")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"申请时间","min-width":"160","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.ApplyTime?e.parseTime(t.row.ApplyTime,"{y}-{m}-{d} {h}:{i}:{s}"):"--")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作","min-width":"120","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{type:"text",size:"mini"},on:{click:function(a){e.toReview(t.row)}}},[e._v("审核")])]}}])})],1):e._e(),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1),e._v(" "),a("el-dialog",{staticClass:"dialog",attrs:{visible:e.reviewDialogVisible,title:"审核客户",width:"480px",center:""},on:{"update:visible":function(t){e.reviewDialogVisible=t}}},[a("el-form",{attrs:{"label-width":"60px"}},[a("el-form-item",{attrs:{label:"理由",prop:"description"}},[a("el-input",{attrs:{type:"textarea",rows:"3"},model:{value:e.dialogData.Note,callback:function(t){e.$set(e.dialogData,"Note",t)},expression:"dialogData.Note"}})],1)],1),e._v(" "),a("div",{attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.doReview("reject")}}},[e._v("拒绝")]),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.doReview("accept")}}},[e._v("同意")])],1)],1),e._v(" "),a("el-dialog",{staticClass:"dialog",attrs:{visible:e.addAgentDialogVisible,title:"邀请客户",width:"480px",center:""},on:{"update:visible":function(t){e.addAgentDialogVisible=t}}},[a("el-row",{staticClass:"add-agent-dialog"},[a("div",{staticClass:"title"},[e._v("请将邀请链接发送给您的客户,客户同意邀请后请及时审核")]),e._v(" "),a("div",{staticClass:"input"},[a("el-input",{attrs:{value:e.addAgentDialogData.url,readonly:""}})],1),e._v(" "),a("div",{staticClass:"image"},[a("img",{attrs:{src:"https://crmweb.netbcloud.com/uploads/tencentQR/tencentQR.jpg"}}),e._v(" "),a("div",[a("h4",[e._v("邀请二维码")]),e._v(" "),a("div",[a("el-button",{attrs:{type:"text"},on:{click:e.downsQRCodeImage}},[e._v("小尺寸下载 ")]),e._v(" 258*258px，适合网络分享")],1),e._v(" "),a("div",[a("el-button",{attrs:{type:"text"},on:{click:e.downsQRCodeImage}},[e._v("大尺寸下载 ")]),e._v(" 1280*1280px，适合海报印制")],1)])])]),e._v(" "),a("div",{attrs:{slot:"footer"},slot:"footer"},[a("el-button",{staticClass:"bg-green",attrs:{type:"primary"},on:{click:function(t){e.addAgentDialogVisible=!1}}},[e._v("确定")])],1)],1)],1)},[],!1,null,"0178b1d7",null);s.options.__file="index.vue";t.default=s.exports},"7T8G":function(e,t,a){},H750:function(e,t,a){"use strict";var n=a("7T8G");a.n(n).a},"zAZ/":function(e,t,a){"use strict";a.d(t,"e",function(){return l}),a.d(t,"c",function(){return s}),a.d(t,"d",function(){return i}),a.d(t,"a",function(){return o}),a.d(t,"f",function(){return c}),a.d(t,"b",function(){return u});var n=a("EJiy"),r=a.n(n);function l(e){var t=e.column,a=e.prop,n=e.order;this.order={column:t,prop:a,order:n},this.getData({sortName:a,sortOrder:n&&("ascending"===n?"asc":"desc")})}function s(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function i(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function o(e){this.th[e].check=!this.th[e].check}function c(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",n=void 0;"object"===(void 0===e?"undefined":r()(e))?n=e:(10===(""+e).length&&(e=1e3*parseInt(e)),n=new Date(e));var l={y:n.getFullYear(),m:n.getMonth()+1,d:n.getDate(),h:n.getHours(),i:n.getMinutes(),s:n.getSeconds(),a:n.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=l[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}function u(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var a=new Date(t),n=new Date;n=n.valueOf();var r=Math.ceil((a-n)/864e5);return 0===r?"今天":r>0&&r<30?r+"天":r>=30&&r<360?Math.ceil(r/30)+"月":r>=360?Math.ceil(r/360)+"年":"逾期"+Math.ceil((n-a)/864e5)+"天"}}}]);