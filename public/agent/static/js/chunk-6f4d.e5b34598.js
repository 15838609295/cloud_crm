(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-6f4d"],{"0rbn":function(e,t,a){"use strict";var n=a("mySb");a.n(n).a},"8npP":function(e,t,a){"use strict";a.r(t);var n={props:{checkItems:{type:Array,default:function(){return[]}}},data:function(){return{checkList:[]}},created:function(){for(var e in this.checkItems)this.checkItems[e].check&&this.checkList.push(this.checkItems[e].name)},methods:{change:function(e){this.$emit("change",e)}}},r=(a("MO55"),a("KHd+")),i=Object(r.a)(n,function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("el-dropdown",{staticClass:"controller",attrs:{"hide-on-click":!1}},[n("img",{attrs:{src:a("vtJi")}}),e._v(" "),n("el-dropdown-menu",{attrs:{slot:"dropdown"},slot:"dropdown"},[n("div",{staticClass:"title"},[e._v("列控制器")]),e._v(" "),n("el-checkbox-group",{attrs:{min:1},model:{value:e.checkList,callback:function(t){e.checkList=t},expression:"checkList"}},e._l(e.checkItems,function(t,a){return n("el-dropdown-item",{key:a,attrs:{divided:0==a}},[n("el-checkbox",{attrs:{label:t.name},on:{change:function(t){e.change(a)}}})],1)}))],1)],1)},[],!1,null,"caf1a010",null);i.options.__file="ColumnController.vue";var s=i.exports,o=a("zAZ/"),c={components:{ColumnController:s},data:function(){return{searchTerms:{searchValue:""},dialogVisible:!1,payWay:0,selectOrder:null,tableMaxHeight:document.documentElement.clientHeight-280,tableLoading:!1,currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,pageSizes:[10,20,50,100,1e3],total:0,tableData:[],th:[{name:"ID",check:!0},{name:"商品名称",check:!0},{name:"订单号",check:!0},{name:"商品类型",check:!0},{name:"购买用户",check:!0},{name:"单价数量",check:!0},{name:"总价",check:!0},{name:"交易时间",check:!0},{name:"提交人",check:!0},{name:"支付状态",check:!0},{name:"订单状态",check:!0},{name:"操作",check:!0}]}},created:function(){this.getData()},methods:{handleCurrentChange:o.b,handleSizeChange:o.c,handleSortChange:o.d,columnCheck:o.a,parseTime:o.e,getData:function(e){var t=this;this.tableLoading=!0,e||(e={});var a={pageNumber:this.currentPage,pageSize:this.pageSize,search:this.searchTerms.searchValue};e.sortName&&(a.sortName=e.sortName),e.sortOrder&&(a.sortOrder=e.sortOrder),this.$store.dispatch("GetConnect",{url:"order/index",data:a}).then(function(e){t.tableData=e.data.rows,t.total=e.data.total,t.tableLoading=!1}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},confirmOrder:function(e,t){var a=this;this.$confirm(("cancel"===t?"取消":"pay"===t?"支付":"refund"===t?"申请退款":"完成")+"该订单, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var n={id:e.id,c:t,pay_type:"pay"===t?a.payWay:""};a.$store.dispatch("GetConnect",{url:"order/del",data:n}).then(function(){a.getData(),a.$message.success("操作成功"),a.dialogVisible=!1}).catch(function(e){a.$message.error(e.msg)})})},showBuyDialog:function(e){this.dialogVisible=!0,this.selectOrder=e}}},l=(a("0rbn"),Object(r.a)(c,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-card",{staticClass:"box-1"},[a("div",{staticClass:"header"},[a("div",[a("el-input",{staticClass:"search-input",attrs:{placeholder:"请输入内容（商品名称）"},on:{change:e.getData},model:{value:e.searchTerms.searchValue,callback:function(t){e.$set(e.searchTerms,"searchValue",t)},expression:"searchTerms.searchValue"}}),e._v(" "),a("el-button",{staticClass:"do-btn",attrs:{type:"success",icon:"el-icon-search"},on:{click:e.getData}},[e._v("搜索")])],1),e._v(" "),a("column-controller",{staticStyle:{float:"right"},attrs:{"check-items":e.th},on:{change:e.columnCheck}})],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange}},[e.th[0].check?a("el-table-column",{attrs:{prop:"id",label:"ID","min-width":"65","header-align":"center",align:"center",sortable:""}}):e._e(),e._v(" "),e.th[1].check?a("el-table-column",{attrs:{prop:"title",label:"商品名称","min-width":"250","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[2].check?a("el-table-column",{attrs:{prop:"order_sn",label:"订单号","min-width":"150","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[3].check?a("el-table-column",{attrs:{prop:"type",label:"商品类型","min-width":"110","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(0===t.row.type?"小程序":1===t.row.type?"腾讯云":"定制开发")+"\n        ")]}}])}):e._e(),e._v(" "),e.th[4].check?a("el-table-column",{attrs:{prop:"uname",label:"购买用户","min-width":"130","header-align":"center",align:"center"}}):e._e(),e._v(" "),a("el-table-column",{attrs:{prop:"owner_uin",label:"腾讯账号ID","min-width":"130","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.owner_uin?t.row.owner_uin:"--")+"\n        ")]}}])}),e._v(" "),e.th[5].check?a("el-table-column",{attrs:{label:"单价数量","min-width":"140","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.price+"元/"+(0===t.row.long?"永久":1===t.row.long?"年":2===t.row.long?"月":3===t.row.long?"日":"")+"×"+t.row.amount)+"\n        ")]}}])}):e._e(),e._v(" "),e.th[6].check?a("el-table-column",{attrs:{prop:"total_price",label:"总价","min-width":"110","header-align":"center",align:"center",sortable:""},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-tag",{attrs:{type:"primary"}},[e._v(e._s(t.row.total_price))])]}}])}):e._e(),e._v(" "),e.th[7].check?a("el-table-column",{attrs:{prop:"pay_time",label:"交易时间","min-width":"160","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.pay_time?e.parseTime(t.row.pay_time,"{y}-{m}-{d} {h}:{i}:{s}"):"--")+"\n        ")]}}])}):e._e(),e._v(" "),e.th[9].check?a("el-table-column",{attrs:{prop:"pay_status",label:"支付状态","min-width":"100","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(-3===t.row.pay_status?"拒绝退款":-2===t.row.pay_status?"已退款":-1===t.row.pay_status?"申请退款":0===t.row.pay_status?"待付款":1===t.row.pay_status?"已付款":"已完成")+"\n        ")]}}])}):e._e(),e._v(" "),e.th[10].check?a("el-table-column",{attrs:{prop:"status",label:"订单状态","min-width":"100","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(-2===t.row.status?"已取消":-1===t.row.status?"退款中":1===t.row.status?"已完成":0===t.row.status&&0===t.row.pay_status?"待付款":"待处理")+"\n        ")]}}])}):e._e(),e._v(" "),e.th[11].check?a("el-table-column",{attrs:{label:"操作","min-width":"200","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[0===t.row.status&&0===t.row.pay_status?a("el-button",{attrs:{type:"danger",size:"mini",round:""},on:{click:function(a){e.confirmOrder(t.row,"cancel")}}},[e._v("取消订单")]):e._e(),e._v(" "),0===t.row.status&&1===t.row.pay_status?a("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"mini",round:""},on:{click:function(a){e.confirmOrder(t.row,"refund")}}},[e._v("申请退款")]):e._e(),e._v(" "),0===t.row.status&&0===t.row.pay_status?a("el-button",{attrs:{type:"warning",size:"mini",round:""},on:{click:function(a){e.showBuyDialog(t.row)}}},[e._v("去支付")]):e._e()]}}])}):e._e()],1),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1),e._v(" "),a("el-dialog",{staticClass:"dialog",attrs:{visible:e.dialogVisible,title:"支付方式",width:"560px",center:""},on:{"update:visible":function(t){e.dialogVisible=t}}},[a("div",{staticStyle:{"text-align":"center"}},[a("el-radio-group",{model:{value:e.payWay,callback:function(t){e.payWay=t},expression:"payWay"}},[a("el-radio",{attrs:{label:0}},[e._v("资金支付（剩余："+e._s(e.$store.getters.balance)+"）")]),e._v(" "),a("el-radio",{attrs:{label:1}},[e._v("赠送金支付（剩余："+e._s(e.$store.getters.cashCoupon)+"）")])],1)],1),e._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.dialogVisible=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{staticClass:"bg-green",attrs:{type:"primary"},on:{click:function(t){e.confirmOrder(e.selectOrder,"pay")}}},[e._v("确 定")])],1)])],1)},[],!1,null,"28e2556f",null));l.options.__file="orders.vue";t.default=l.exports},MO55:function(e,t,a){"use strict";var n=a("gEZL");a.n(n).a},gEZL:function(e,t,a){},mySb:function(e,t,a){},vtJi:function(e,t){e.exports="data:image/png;base64,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"},"zAZ/":function(e,t,a){"use strict";a.d(t,"d",function(){return i}),a.d(t,"b",function(){return s}),a.d(t,"c",function(){return o}),a.d(t,"a",function(){return c}),a.d(t,"e",function(){return l});var n=a("EJiy"),r=a.n(n);function i(e){var t=e.column,a=e.prop,n=e.order;this.order={column:t,prop:a,order:n},this.getData({sortName:a,sortOrder:n&&("ascending"===n?"asc":"desc")})}function s(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function o(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function c(e){this.th[e].check=!this.th[e].check}function l(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",n=void 0;"object"===(void 0===e?"undefined":r()(e))?n=e:(10===(""+e).length&&(e=1e3*parseInt(e)),n=new Date(e));var i={y:n.getFullYear(),m:n.getMonth()+1,d:n.getDate(),h:n.getHours(),i:n.getMinutes(),s:n.getSeconds(),a:n.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=i[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}}}]);