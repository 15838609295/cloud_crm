(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-3ccd"],{"3c+Q":function(e,t,a){},MziO:function(e,t,a){"use strict";a.r(t);var r=a("gDS+"),n=a.n(r),s=a("YbJm"),i=a("zAZ/"),c={components:{ColumnController:s.a},data:function(){return{isShow:!1,searchTerms:{branch_id:"",user_id:"",state:"",date:[],surplus_time:"",searchValue:""},branchList:[],usersList:[],allMemberList:[],statusList:[{id:0,label:"进行中"},{id:1,label:"已结束"},{id:2,label:"已禁用"}],lastTimeList:[],dialogVisible:!1,transfer:{exchange_user:"",exchange_list:""},percentage_money:"",tableMaxHeight:document.documentElement.clientHeight-380,tableLoading:!1,multipleSelection:[],currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,pageSizes:[10,20,50,100,1e3],total:0,tableData:[],th:[{name:"商品名称",check:!0},{name:"负责人",check:!0},{name:"订单号",check:!0},{name:"客户名称",check:!0},{name:"客户手机",check:!0},{name:"商品售价",check:!0},{name:"售后提成",check:!0},{name:"购买时间",check:!0},{name:"售后时间(月)",check:!0},{name:"剩余提成",check:!0},{name:"订单状态",check:!0}]}},watch:{$route:function(e){var t=this;"售后订单"===e.name?(this.getData(),setTimeout(function(){t.isShow=!0},500)):this.isShow=!1}},created:function(){if("售后订单"===this.$route.name){this.isShow=!0,this.getData(),this.getBranchList(),this.getUsersList();for(var e=1;e<26;e++)e<25&&this.lastTimeList.push({id:e,label:e+"个月"}),25===e&&this.lastTimeList.push({id:e,label:"两年以上"})}},methods:{handleCurrentChange:i.c,handleSizeChange:i.d,handleSortChange:i.e,columnCheck:i.a,parseTime:i.f,getData:function(e){var t=this;this.tableLoading=!0,e||(e={});var a={page_no:this.currentPage,page_size:this.pageSize,search:this.searchTerms.searchValue,branch_id:this.searchTerms.branch_id,status:this.searchTerms.status,user_id:this.searchTerms.user_id,surplus_time:this.searchTerms.surplus_time};this.searchTerms.date&&0!==this.searchTerms.date.length&&(a.start_time=this.searchTerms.date[0]+" 00:00:00",a.end_time=this.searchTerms.date[1]+" 23:59:59"),e.sortName&&(a.sortName=e.sortName),e.sortOrder&&(a.sortOrder=e.sortOrder),this.$store.dispatch("GetConnect",{url:"aftersale/list",data:a}).then(function(e){t.tableData=e.data.rows,t.percentage_money=e.data.percentage_money,t.total=e.data.total;for(var a=0,r=0,n=0,s=0,i=0,c=0,l=0;l<t.tableData.length;l++)a=a+.3*parseFloat(t.tableData[l].after_money||0)+(parseFloat(t.tableData[l].after_money||0)-.3*parseFloat(t.tableData[l].after_money||0))*(parseInt(t.tableData[l].after_time)-1)/parseInt(t.tableData[l].buy_length-1),r=r+parseFloat(t.tableData[l].bonus_royalty||0)-parseFloat(t.tableData[l].over_bonus_price||0),n+=parseFloat(t.tableData[l].after_money||0),s+=parseFloat(t.tableData[l].bonus_royalty||0),i+=parseFloat(t.tableData[l].over_price||0),c+=parseFloat(t.tableData[l].over_bonus_price||0);console.log("==============================="),console.log("统计售后订单总数: "+t.tableData.length),console.log("提成总金额: "+n.toFixed(2)+", 总奖金:"+s.toFixed(2)+", 总金额: "+(n+s).toFixed(2)),console.log("实际服务提成: "+(i-n).toFixed(2)+", 实际服务奖金:"+r.toFixed(2)+", 实际服务总金额: "+(i-n+r).toFixed(2)),console.log("未发提成: "+i.toFixed(2)+", 未发奖金:"+c.toFixed(2)+", 未发总金额: "+(i+c).toFixed(2)),console.log("==============================="),t.tableLoading=!1}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},getBranchList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"branch/all-list"}).then(function(t){e.branchList=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},getUsersList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"user/all-list"}).then(function(t){e.usersList=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},getAllMemberList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"user/admin-list"}).then(function(t){e.allMemberList=t.data.list}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},toggleSelection:function(e){var t=this;e?e.forEach(function(e){t.$refs.multipleTable.toggleRowSelection(e)}):this.$refs.multipleTable.clearSelection()},handleSelectionChange:function(e){this.multipleSelection=e},toTransfer:function(){var e=this;this.$confirm("此操作将转移该售后订单, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var t={exchange_user:e.transfer.exchange_user,exchange_list:[]},a=[];for(var r in e.multipleSelection)a.push(e.multipleSelection[r].id);t.exchange_list=n()(a),e.$store.dispatch("GetConnect",{url:"aftersale/exchangeOrder",data:t}).then(function(t){e.$message.success(t.msg),e.getData(),e.dialogVisible=!1}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员"),e.dialogVisible=!1})})},toModify:function(e,t){this.isShow=!1;var a={title:e};t&&(a.id=t.id),this.$router.push({path:"/orders/afterSale/modify",query:a})},toProhibit:function(e){var t=this;this.$confirm("此操作将"+(0===e.after_status?"禁用":"启用")+"该售后订单, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var a="aftersale/ajax/"+e.id+"?action="+(0===e.after_status?"prohibit":"open");t.$store.dispatch("GetConnect",{url:a}).then(function(t){0===e.after_status?e.after_status=2:e.after_status=0}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员")})})},toDelete:function(e){var t=this;this.$confirm("此操作将删除该售后订单, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var a="aftersale/delete/"+e.id;t.$store.dispatch("GetConnect",{url:a}).then(function(e){t.$message.success("删除成功！"),t.getData()}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员")})})}}},l=(a("zWXq"),a("KHd+")),o=Object(l.a)(c,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("transition",{attrs:{name:"fade-transform",mode:"out-in"}},[a("router-view")],1),e._v(" "),e.isShow?a("el-card",{staticClass:"box-1"},[a("el-row",{staticClass:"model",attrs:{gutter:20}},[a("el-col",{attrs:{xs:12,sm:8,md:7,lg:6,xl:4}},[a("el-card",{attrs:{shadow:"hover"}},[a("span",[e._v("售后提成总额")]),e._v(" "),a("div",[a("span",[e._v("￥ ")]),e._v(" "+e._s(e.percentage_money))])])],1),e._v(" "),a("el-col",{attrs:{xs:12,sm:8,md:7,lg:18,xl:20}},[a("div",{staticClass:"header"},[a("div",[a("el-select",{staticClass:"select",attrs:{placeholder:"团队",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.branch_id,callback:function(t){e.$set(e.searchTerms,"branch_id",t)},expression:"searchTerms.branch_id"}},e._l(e.branchList,function(e){return a("el-option",{key:e.id,attrs:{label:e.branch_name,value:e.id}})})),e._v(" "),a("el-select",{staticClass:"select",attrs:{placeholder:"负责人",filterable:"",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.user_id,callback:function(t){e.$set(e.searchTerms,"user_id",t)},expression:"searchTerms.user_id"}},e._l(e.usersList,function(e){return a("el-option",{key:e.id,attrs:{label:e.name,value:e.id}})})),e._v(" "),a("el-select",{staticClass:"select",attrs:{placeholder:"售后剩余时间",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.surplus_time,callback:function(t){e.$set(e.searchTerms,"surplus_time",t)},expression:"searchTerms.surplus_time"}},e._l(e.lastTimeList,function(e){return a("el-option",{key:e.id,attrs:{label:e.label,value:e.id}})})),e._v(" "),a("el-select",{staticClass:"select",attrs:{placeholder:"状态",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.status,callback:function(t){e.$set(e.searchTerms,"status",t)},expression:"searchTerms.status"}},e._l(e.statusList,function(e){return a("el-option",{key:e.id,attrs:{label:e.label,value:e.id}})})),e._v(" "),a("el-date-picker",{staticClass:"picker",attrs:{editable:!1,type:"daterange","range-separator":"-","start-placeholder":"开始日期","end-placeholder":"结束日期","value-format":"yyyy-MM-dd"},on:{change:e.getData},model:{value:e.searchTerms.date,callback:function(t){e.$set(e.searchTerms,"date",t)},expression:"searchTerms.date"}}),e._v(" "),a("el-input",{staticClass:"search-input",attrs:{placeholder:"客户名,手机,商品名,订单号"},on:{change:e.getData},model:{value:e.searchTerms.searchValue,callback:function(t){e.$set(e.searchTerms,"searchValue",t)},expression:"searchTerms.searchValue"}}),e._v(" "),a("el-button",{staticClass:"do-btn",attrs:{type:"success",icon:"el-icon-search"},on:{click:e.getData}},[e._v("搜索")])],1),e._v(" "),a("div",[e.queryMatch(95)?a("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-plus"},on:{click:function(t){e.toModify("售后订单-创建")}}},[e._v("添加")]):e._e(),e._v(" "),e.queryMatch(116)?a("el-button",{attrs:{type:"danger",icon:"el-icon-share"},on:{click:function(t){e.dialogVisible=!0}}},[e._v("转移")]):e._e(),e._v(" "),a("span",{staticClass:"flex-1"}),e._v(" "),a("column-controller",{attrs:{"check-items":e.th},on:{change:e.columnCheck}})],1)])])],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange,"selection-change":e.handleSelectionChange}},[a("el-table-column",{attrs:{type:"selection",width:"50","header-align":"center",align:"center"}}),e._v(" "),e.th[0].check?a("el-table-column",{attrs:{prop:"goods_name",label:"商品名称","min-width":"160","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[1].check?a("el-table-column",{attrs:{prop:"after_name",label:"负责人","min-width":"80","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[2].check?a("el-table-column",{attrs:{prop:"order_number",label:"订单号","min-width":"140","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[3].check?a("el-table-column",{attrs:{prop:"member_name",label:"客户名称","min-width":"120","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[4].check?a("el-table-column",{attrs:{prop:"member_phone",label:"手机","min-width":"120","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[5].check?a("el-table-column",{attrs:{prop:"goods_money",label:"商品金额","min-width":"120","header-align":"center",align:"center",sortable:""}}):e._e(),e._v(" "),e.th[6].check?a("el-table-column",{attrs:{prop:"after_money",label:"售后费用","min-width":"110","header-align":"center",align:"center",sortable:""}}):e._e(),e._v(" "),e.th[6].check?a("el-table-column",{attrs:{prop:"bonus_royalty",label:"奖金","empty-text":"--","min-width":"110","header-align":"center",align:"center",sortable:""}}):e._e(),e._v(" "),e.th[7].check?a("el-table-column",{attrs:{prop:"buy_time",label:"购买时间","min-width":"120","header-align":"center",align:"center",sortable:""},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(e.parseTime(t.row.buy_time,"{y}-{m}-{d}"))+"\n        ")]}}])}):e._e(),e._v(" "),e.th[8].check?a("el-table-column",{attrs:{prop:"after_time",label:"剩余售后时间(月)","min-width":"135","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.buy_length-t.row.after_time+" / "+t.row.buy_length)+"\n        ")]}}])}):e._e(),e._v(" "),e.th[9].check?a("el-table-column",{attrs:{prop:"over_price",label:"剩下提成","min-width":"100","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[9].check?a("el-table-column",{attrs:{prop:"over_bonus_price",label:"剩下奖金","empty-text":"--","min-width":"100","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[10].check?a("el-table-column",{attrs:{prop:"after_status",label:"状态","min-width":"100","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",{on:{click:function(a){e.queryMatch(96)&&e.toProhibit(t.row)}}},[a("el-tag",{attrs:{type:0===t.row.after_status?"success":1===t.row.after_status?"info":"danger"}},[e._v("\n              "+e._s(0===t.row.after_status?"进行中":1===t.row.after_status?"已结束":"已禁用")+"\n            ")])],1)]}}])}):e._e(),e._v(" "),a("el-table-column",{attrs:{label:"操作","min-width":"200","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{type:"primary",size:"small",icon:"el-icon-search",circle:""},on:{click:function(a){e.toModify("售后订单-详情",t.row)}}}),e._v(" "),e.queryMatch(96)?a("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small",icon:"el-icon-edit",circle:""},on:{click:function(a){e.toModify("售后订单-编辑",t.row)}}}):e._e(),e._v(" "),0!==t.row.after_status&&e.queryMatch(97)?a("el-button",{attrs:{type:"danger",size:"small",icon:"el-icon-delete",circle:""},on:{click:function(a){e.toDelete(t.row)}}}):e._e()]}}])})],1),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1):e._e(),e._v(" "),a("el-dialog",{staticClass:"dialog",attrs:{visible:e.dialogVisible,title:"订单转移",width:"360px",center:""},on:{"update:visible":function(t){e.dialogVisible=t}}},[a("div",{staticClass:"content"},[a("div",{staticClass:"text"},[e._v("将所选 "+e._s(e.multipleSelection.length)+" 条售后订单转移至")]),e._v(" "),a("el-select",{attrs:{placeholder:"请选择转移用户",filterable:""},model:{value:e.transfer.exchange_user,callback:function(t){e.$set(e.transfer,"exchange_user",t)},expression:"transfer.exchange_user"}},e._l(e.usersList,function(e){return a("el-option",{key:e.id,attrs:{label:e.name,value:e.id}})}))],1),e._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.dialogVisible=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:e.toTransfer}},[e._v("确 定")])],1)])],1)},[],!1,null,"7ce378d8",null);o.options.__file="index.vue";t.default=o.exports},Rgla:function(e,t,a){"use strict";var r=a("rUAo");a.n(r).a},YbJm:function(e,t,a){"use strict";var r={props:{checkItems:{type:Array,default:function(){return[]}}},data:function(){return{checkList:[]}},created:function(){for(var e in this.checkItems)this.checkItems[e].check&&this.checkList.push(this.checkItems[e].name)},methods:{change:function(e){this.$emit("change",e)}}},n=(a("Rgla"),a("KHd+")),s=Object(n.a)(r,function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("el-dropdown",{staticClass:"controller",attrs:{"hide-on-click":!1}},[r("img",{attrs:{src:a("vtJi")}}),e._v(" "),r("el-dropdown-menu",{attrs:{slot:"dropdown"},slot:"dropdown"},[r("div",{staticClass:"title"},[e._v("列控制器")]),e._v(" "),r("el-checkbox-group",{attrs:{min:1},model:{value:e.checkList,callback:function(t){e.checkList=t},expression:"checkList"}},e._l(e.checkItems,function(t,a){return r("el-dropdown-item",{key:a,attrs:{divided:0==a}},[r("el-checkbox",{attrs:{label:t.name},on:{change:function(t){e.change(a)}}})],1)}))],1)],1)},[],!1,null,"39289c7c",null);s.options.__file="ColumnController.vue";var i=s.exports;a.d(t,"a",function(){return i})},rUAo:function(e,t,a){},vtJi:function(e,t){e.exports="data:image/png;base64,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"},"zAZ/":function(e,t,a){"use strict";a.d(t,"e",function(){return s}),a.d(t,"c",function(){return i}),a.d(t,"d",function(){return c}),a.d(t,"a",function(){return l}),a.d(t,"f",function(){return o}),a.d(t,"b",function(){return h});var r=a("EJiy"),n=a.n(r);function s(e){var t=e.column,a=e.prop,r=e.order;this.order={column:t,prop:a,order:r},this.getData({sortName:a,sortOrder:r&&("ascending"===r?"asc":"desc")})}function i(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function c(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function l(e){this.th[e].check=!this.th[e].check}function o(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",r=void 0;"object"===(void 0===e?"undefined":n()(e))?r=e:(10===(""+e).length&&(e=1e3*parseInt(e)),r=new Date(e));var s={y:r.getFullYear(),m:r.getMonth()+1,d:r.getDate(),h:r.getHours(),i:r.getMinutes(),s:r.getSeconds(),a:r.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=s[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}function h(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var a=new Date(t),r=new Date;r=r.valueOf();var n=Math.ceil((a-r)/864e5);return 0===n?"今天":n>0&&n<30?n+"天":n>=30&&n<360?Math.ceil(n/30)+"月":n>=360?Math.ceil(n/360)+"年":"逾期"+Math.ceil((r-a)/864e5)+"天"}},zWXq:function(e,t,a){"use strict";var r=a("3c+Q");a.n(r).a}}]);