(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-cf68"],{"/X4m":function(e,t,n){"use strict";var a=n("ruEu");n.n(a).a},Rgla:function(e,t,n){"use strict";var a=n("rUAo");n.n(a).a},YbJm:function(e,t,n){"use strict";var a={props:{checkItems:{type:Array,default:function(){return[]}}},data:function(){return{checkList:[]}},created:function(){for(var e in this.checkItems)this.checkItems[e].check&&this.checkList.push(this.checkItems[e].name)},methods:{change:function(e){this.$emit("change",e)}}},c=(n("Rgla"),n("KHd+")),o=Object(c.a)(a,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("el-dropdown",{staticClass:"controller",attrs:{"hide-on-click":!1}},[a("img",{attrs:{src:n("vtJi")}}),e._v(" "),a("el-dropdown-menu",{attrs:{slot:"dropdown"},slot:"dropdown"},[a("div",{staticClass:"title"},[e._v("列控制器")]),e._v(" "),a("el-checkbox-group",{attrs:{min:1},model:{value:e.checkList,callback:function(t){e.checkList=t},expression:"checkList"}},e._l(e.checkItems,function(t,n){return a("el-dropdown-item",{key:n,attrs:{divided:0==n}},[a("el-checkbox",{attrs:{label:t.name},on:{change:function(t){e.change(n)}}})],1)}))],1)],1)},[],!1,null,"39289c7c",null);o.options.__file="ColumnController.vue";var s=o.exports;n.d(t,"a",function(){return s})},rUAo:function(e,t,n){},ruEu:function(e,t,n){},sdl1:function(e,t,n){"use strict";n.r(t);var a={components:{ColumnController:n("YbJm").a},data:function(){return{searchTerms:{input:""},currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,total:30,filter:{type:[{text:"小程序",value:"小程序"},{text:"腾讯云",value:"腾讯云"}]},multipleSelection:[],tableData:[{customer_name:"朱峰",customer_phone:"15622283843",order_num:"19054894531515",goods_name:"分销商城小程序(营销版+客服)",goods_type:"小程序",goods_price:"1980.00",manage:"许堉颖",date:"2018-09-19",remainDays:8,status:0},{customer_name:"朱峰",customer_phone:"15622283843",order_num:"19054894531515",goods_name:"分销商城小程序(营销版+客服)",goods_type:"小程序",goods_price:"1980.00",manage:"许堉颖",date:"2018-09-19",remainDays:8,status:0},{customer_name:"朱峰",customer_phone:"15622283843",order_num:"19054894531515",goods_name:"分销商城小程序(营销版+客服)",goods_type:"小程序",goods_price:"1980.00",manage:"许堉颖",date:"2018-09-19",remainDays:8,status:0},{customer_name:"朱峰",customer_phone:"15622283843",order_num:"19054894531515",goods_name:"分销商城小程序(营销版+客服)",goods_type:"小程序",goods_price:"1980.00",manage:"许堉颖",date:"2018-09-19",remainDays:8,status:0},{customer_name:"朱峰",customer_phone:"15622283843",order_num:"19054894531515",goods_name:"分销商城小程序(营销版+客服)",goods_type:"小程序",goods_price:"1980.00",manage:"许堉颖",date:"2018-09-19",remainDays:8,status:0},{customer_name:"朱峰",customer_phone:"15622283843",order_num:"19054894531515",goods_name:"分销商城小程序(营销版+客服)",goods_type:"小程序",goods_price:"1980.00",manage:"许堉颖",date:"2018-09-19",remainDays:8,status:0},{customer_name:"朱峰",customer_phone:"15622283843",order_num:"19054894531515",goods_name:"分销商城小程序(营销版+客服)",goods_type:"小程序",goods_price:"1980.00",manage:"许堉颖",date:"2018-09-19",remainDays:8,status:0}],th:[{name:"购买用户",check:!0},{name:"手机号码",check:!0},{name:"订单号",check:!0},{name:"商品名称",check:!0},{name:"商品类型",check:!0},{name:"商品金额",check:!0},{name:"负责人",check:!0},{name:"购买时间",check:!0},{name:"剩余天数",check:!0},{name:"状态",check:!0}]}},methods:{columnCheck:function(e){this.th[e].check=!this.th[e].check},filterTag:function(e,t){return t.type===e},handleSizeChange:function(e){},handleCurrentChange:function(e){},handleSelectionChange:function(e){this.multipleSelection=e},search:function(){},toSendAllMsg:function(){var e=this;0!==this.multipleSelection.length&&this.$confirm("即将发送短信给"+this.multipleSelection[0].customer_name+"等"+this.multipleSelection.length+"位客户, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消"}).then(function(){e.$message({type:"success",message:"发送成功!"})})},toCall:function(e){var t=this;this.$confirm("即将打电话给"+e.customer_name+", 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消"}).then(function(){t.$message({type:"success",message:"成功!"})})},toSendMsg:function(e){var t=this;this.$confirm("即将发送短信给"+e.customer_name+", 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消"}).then(function(){t.$message({type:"success",message:"发送成功!"})})},toDelete:function(){var e=this;this.$confirm("此操作将删除该订单提醒, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){e.$message({type:"success",message:"删除成功!"})})}}},c=(n("/X4m"),n("KHd+")),o=Object(c.a)(a,function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("div",{staticClass:"app-container bg-gray"},[n("el-card",{staticClass:"box-1"},[n("div",{staticClass:"header"},[n("div",[n("el-input",{staticClass:"search-input",attrs:{placeholder:"请输入内容"},on:{change:e.search},model:{value:e.searchTerms.input,callback:function(t){e.$set(e.searchTerms,"input",t)},expression:"searchTerms.input"}}),e._v(" "),n("el-button",{staticClass:"do-btn",attrs:{type:"success",icon:"el-icon-search"},on:{click:e.search}},[e._v("搜索")]),e._v(" "),n("el-button",{attrs:{type:"danger",icon:"el-icon-bell"},on:{click:e.toSendAllMsg}},[e._v("批量提醒")])],1),e._v(" "),n("column-controller",{staticStyle:{float:"right"},attrs:{"check-items":e.th},on:{change:e.columnCheck}})],1),e._v(" "),n("el-table",{staticStyle:{width:"100%"},attrs:{data:e.tableData,"default-sort":{prop:"id",order:"descending"},border:""},on:{"selection-change":e.handleSelectionChange}},[n("el-table-column",{attrs:{type:"selection",width:"50","header-align":"center",align:"center"}}),e._v(" "),e.th[0].check?n("el-table-column",{attrs:{prop:"customer_name",label:"购买用户","min-width":"100","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[1].check?n("el-table-column",{attrs:{prop:"customer_phone",label:"手机号码","min-width":"120","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[2].check?n("el-table-column",{attrs:{prop:"order_num",label:"订单号","min-width":"140","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[3].check?n("el-table-column",{attrs:{prop:"goods_name",label:"商品名称","min-width":"220","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[4].check?n("el-table-column",{attrs:{filters:e.filter.type,"filter-method":e.filterTag,"filter-placement":"bottom-end",prop:"goods_type",label:"商品类型","min-width":"110","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[5].check?n("el-table-column",{attrs:{prop:"goods_price",label:"商品金额","min-width":"120","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[6].check?n("el-table-column",{attrs:{prop:"manage",label:"负责人","min-width":"80","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[7].check?n("el-table-column",{attrs:{prop:"date",label:"购买时间","min-width":"130","header-align":"center",align:"center"}}):e._e(),e._v(" "),e.th[8].check?n("el-table-column",{attrs:{prop:"remainDays",label:"剩余天数","min-width":"110","header-align":"center",align:"center",sortable:""}}):e._e(),e._v(" "),e.th[9].check?n("el-table-column",{attrs:{label:"状态","min-width":"120","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[n("el-tag",{attrs:{type:0===t.row.status?"danger":"success"}},[e._v(e._s(0===t.row.status?"未联系":"已联系"))])]}}])}):e._e(),e._v(" "),n("el-table-column",{attrs:{label:"操作","min-width":"160","header-align":"center",align:"center",fixed:"right"},scopedSlots:e._u([{key:"default",fn:function(t){return[n("el-tooltip",{attrs:{content:"电话联系",placement:"top"}},[n("el-button",{attrs:{type:"primary",size:"small",icon:"el-icon-phone",circle:""},on:{click:function(n){e.toCall(t.row)}}})],1),e._v(" "),n("el-tooltip",{attrs:{content:"短信提醒",placement:"top"}},[n("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small",icon:"el-icon-message",circle:""},on:{click:function(n){e.toSendMsg(t.row)}}})],1),e._v(" "),n("el-button",{attrs:{type:"danger",size:"small",icon:"el-icon-close",circle:""},on:{click:e.toDelete}})]}}])})],1),e._v(" "),n("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,total:e.total,layout:"total, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange}})],1)],1)},[],!1,null,"ab3d116e",null);o.options.__file="expireOrder.vue";t.default=o.exports},vtJi:function(e,t){e.exports="data:image/png;base64,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"}}]);