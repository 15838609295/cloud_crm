(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-44e9"],{LrGb:function(e,t,a){},Rgla:function(e,t,a){"use strict";var r=a("rUAo");a.n(r).a},YbJm:function(e,t,a){"use strict";var r={props:{checkItems:{type:Array,default:function(){return[]}}},data:function(){return{checkList:[]}},created:function(){for(var e in this.checkItems)this.checkItems[e].check&&this.checkList.push(this.checkItems[e].name)},methods:{change:function(e){this.$emit("change",e)}}},n=(a("Rgla"),a("KHd+")),o=Object(n.a)(r,function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("el-dropdown",{staticClass:"controller",attrs:{"hide-on-click":!1}},[r("img",{attrs:{src:a("vtJi")}}),e._v(" "),r("el-dropdown-menu",{attrs:{slot:"dropdown"},slot:"dropdown"},[r("div",{staticClass:"title"},[e._v("列控制器")]),e._v(" "),r("el-checkbox-group",{attrs:{min:1},model:{value:e.checkList,callback:function(t){e.checkList=t},expression:"checkList"}},e._l(e.checkItems,function(t,a){return r("el-dropdown-item",{key:a,attrs:{divided:0==a}},[r("el-checkbox",{attrs:{label:t.name},on:{change:function(t){e.change(a)}}})],1)}))],1)],1)},[],!1,null,"39289c7c",null);o.options.__file="ColumnController.vue";var s=o.exports;a.d(t,"a",function(){return s})},fLzY:function(e,t,a){"use strict";var r=a("LrGb");a.n(r).a},rUAo:function(e,t,a){},vtJi:function(e,t){e.exports="data:image/png;base64,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"},yAV8:function(e,t,a){"use strict";a.r(t);var r=a("YbJm"),n=a("zAZ/"),o={components:{ColumnController:r.a},data:function(){return{id:null,downloadLoading:!1,searchTerms:{searchValue:"",date:[],type:""},info:{},tableMaxHeight:document.documentElement.clientHeight-280,tableLoading:!1,currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,pageSizes:[10,20,50,100,1e3],total:0,tableData:[],th:[{name:"客户名称",check:!0},{name:"手机",check:!0},{name:"支付金额",check:!0},{name:"余额",check:!0},{name:"操作记录",check:!0},{name:"备注",check:!0},{name:"操作时间",check:!0}],dialogVisible:!1,form:{type:0,doType:1,money:1,remarks:""},formRules:{type:[{required:!0,message:"请选择操作的类型",trigger:"change"}],doType:[{required:!0,message:"请选择增加或减少",trigger:"change"}],money:[{required:!0,message:"请输入提现金额",trigger:"change"}],remarks:[{required:!0,message:"请输入修改备注",trigger:"change"}]}}},watch:{},created:function(){this.id=this.$route.query.id||null,this.getData()},methods:{handleCurrentChange:n.c,handleSizeChange:n.d,handleSortChange:n.e,columnCheck:n.a,getData:function(e){var t=this;this.tableLoading=!0,e||(e={});var a={page_no:this.currentPage,page_size:this.pageSize,search:this.searchTerms.searchValue,type:this.searchTerms.type,id:this.id};this.searchTerms.date&&0!==this.searchTerms.date.length&&(a.start_time=this.searchTerms.date[0]+" 00:00:00",a.end_time=this.searchTerms.date[1]+" 23:59:59"),e.sortName&&(a.sortName=e.sortName),e.sortOrder&&(a.sortOrder=e.sortOrder),this.$store.dispatch("GetConnect",{url:"bonus/mywallet",data:a}).then(function(e){t.tableData=e.data.list.rows,t.info={total_bonus:e.data.total_bonus,expect_bonus:e.data.expect_bonus},t.total=e.data.list.total,t.tableLoading=!1;for(var a=0,r=0;r<t.tableData.length;r++)a+=Number(t.tableData[r].money);console.log("======================"),console.log(a)}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},toDelete:function(){var e=this;this.$confirm("此操作将删除该业绩订单, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){e.$message({type:"success",message:"删除成功!"})})},showDialog:function(){this.dialogVisible=!0},submitDialog:function(e){var t=this;this.$refs[e].validate(function(e){if(e){var a=t.form;a.uid=t.id;t.$store.dispatch("GetConnect",{url:"bonus/capitalOperation",data:a}).then(function(e){t.$message.success(e.msg),t.getData(),t.dialogVisible=!1}).catch(function(e){t.$message.error(e.msg)})}else t.$message.error("提交失败,请检查必填项")})},resetDialog:function(e){this.$refs[e].resetFields()}}},s=(a("fLzY"),a("KHd+")),i=Object(s.a)(o,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-card",{staticClass:"box-1"},[a("el-row",{staticClass:"model",attrs:{gutter:20}},[a("el-col",{attrs:{xs:8,sm:8,md:7,lg:6,xl:4}},[a("el-card",{attrs:{shadow:"hover"}},[a("span",[e._v("我的钱包")]),e._v(" "),a("div",[a("span",[e._v("￥ ")]),e._v(" "+e._s(e.info.total_bonus))])])],1),e._v(" "),a("el-col",{attrs:{xs:8,sm:8,md:7,lg:6,xl:4}},[a("el-card",{attrs:{shadow:"hover"}},[a("span",[e._v("预期奖金")]),e._v(" "),a("div",[a("span",[e._v("￥ ")]),e._v(" "+e._s(e.info.expect_bonus))])])],1),e._v(" "),e.id&&e.queryMatch(187)?a("el-button",{staticStyle:{"margin-right":"15px"},attrs:{type:"danger"},on:{click:e.showDialog}},[e._v("余额修改")]):e._e(),e._v(" "),a("el-select",{staticClass:"select",attrs:{placeholder:"明细类型",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.type,callback:function(t){e.$set(e.searchTerms,"type",t)},expression:"searchTerms.type"}},[a("el-option",{attrs:{value:1,label:"入账明细"}}),e._v(" "),a("el-option",{attrs:{value:2,label:"提现明细"}})],1),e._v(" "),a("el-date-picker",{staticClass:"picker",attrs:{editable:!1,type:"daterange","range-separator":"-","start-placeholder":"开始日期","end-placeholder":"结束日期","value-format":"yyyy-MM-dd"},on:{change:e.getData},model:{value:e.searchTerms.date,callback:function(t){e.$set(e.searchTerms,"date",t)},expression:"searchTerms.date"}})],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange}},[a("el-table-column",{attrs:{prop:"id",label:"ID","min-width":"70","header-align":"center",align:"center",sortable:""}}),e._v(" "),e.th[0].check?a("el-table-column",{attrs:{prop:"member_name",label:"客户名称","min-width":"120","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.member_name||"--")+"\n        ")]}}])}):e._e(),e._v(" "),e.th[1].check?a("el-table-column",{attrs:{prop:"member_phone",label:"手机","min-width":"120","header-align":"center",align:"center",sortable:""},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.member_phone||"--")+"\n        ")]}}])}):e._e(),e._v(" "),e.th[2].check?a("el-table-column",{attrs:{prop:"money",label:"金额","min-width":"110","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-tag",{attrs:{type:0===t.row.money.indexOf("-")?"danger":"success"}},[e._v(e._s(t.row.money))])]}}])}):e._e(),e._v(" "),a("el-table-column",{attrs:{prop:"order_number",label:"订单号","min-width":"110","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.order_number||"--")+"\n        ")]}}])}),e._v(" "),e.th[3].check?a("el-table-column",{attrs:{prop:"goods_money",label:"订单金额","min-width":"110","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.goods_money||"--")+"\n        ")]}}])}):e._e(),e._v(" "),e.th[4].check?a("el-table-column",{attrs:{label:"操作记录","min-width":"300","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(0===t.row.type?"售前提成":1===t.row.type?"售后提成":2===t.row.type?"余额提现":3===t.row.type?"售前退款":4===t.row.type?"售后奖金":5===t.row.type?"奖金提现":6===t.row.type?"增加余额":7===t.row.type?"减少余额":8===t.row.type?"增加奖金":"减少奖金")+"\n          "+e._s((t.row.goods_name?",商品名称"+t.row.goods_name:"")+(t.row.order_number?", 订单号: "+t.row.order_number:""))+"\n        ")]}}])}):e._e(),e._v(" "),e.th[5].check?a("el-table-column",{attrs:{prop:"remarks",label:"备注","min-width":"160"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.remarks?t.row.remarks:"--")+"\n        ")]}}])}):e._e(),e._v(" "),a("el-table-column",{attrs:{label:"操作人","min-width":"120","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.submitter||"--")+"\n        ")]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"审核人","min-width":"120","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n          "+e._s(t.row.auditor||"--")+"\n        ")]}}])}),e._v(" "),e.th[6].check?a("el-table-column",{attrs:{prop:"created_at",label:"发放时间","min-width":"160","header-align":"center",align:"center",sortable:""}}):e._e()],1),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1),e._v(" "),a("el-dialog",{staticClass:"dialog",attrs:{visible:e.dialogVisible,title:"余额修改",width:"480px",center:""},on:{"update:visible":function(t){e.dialogVisible=t},close:function(t){e.resetDialog("form")}}},[a("el-form",{ref:"form",attrs:{model:e.form,rules:e.formRules,"label-width":"90px"}},[a("el-form-item",{attrs:{label:"类型"}},[a("el-radio-group",{attrs:{prop:"type"},model:{value:e.form.type,callback:function(t){e.$set(e.form,"type",t)},expression:"form.type"}},[a("el-radio",{attrs:{label:0}},[e._v("账户余额")]),e._v(" "),a("el-radio",{attrs:{label:1}},[e._v("奖金余额")])],1)],1),e._v(" "),a("el-form-item",{attrs:{label:"操作"}},[a("el-radio-group",{attrs:{prop:"doType"},model:{value:e.form.doType,callback:function(t){e.$set(e.form,"doType",t)},expression:"form.doType"}},[a("el-radio",{attrs:{label:1}},[e._v("增加")]),e._v(" "),a("el-radio",{attrs:{label:0}},[e._v("减少")])],1)],1),e._v(" "),a("el-form-item",{attrs:{label:"金额",prop:"money"}},[a("el-input-number",{attrs:{min:0},model:{value:e.form.money,callback:function(t){e.$set(e.form,"money",t)},expression:"form.money"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"备注",prop:"remarks"}},[a("el-input",{attrs:{type:"textarea",rows:"3"},model:{value:e.form.remarks,callback:function(t){e.$set(e.form,"remarks",t)},expression:"form.remarks"}})],1)],1),e._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.takeCashDialogVisible=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.submitDialog("form")}}},[e._v("确 定")])],1)],1)],1)},[],!1,null,"23f1ef8d",null);i.options.__file="details.vue";t.default=i.exports},"zAZ/":function(e,t,a){"use strict";a.d(t,"e",function(){return o}),a.d(t,"c",function(){return s}),a.d(t,"d",function(){return i}),a.d(t,"a",function(){return l}),a.d(t,"f",function(){return c}),a.d(t,"b",function(){return u});var r=a("EJiy"),n=a.n(r);function o(e){var t=e.column,a=e.prop,r=e.order;this.order={column:t,prop:a,order:r},this.getData({sortName:a,sortOrder:r&&("ascending"===r?"asc":"desc")})}function s(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function i(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function l(e){this.th[e].check=!this.th[e].check}function c(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",r=void 0;"object"===(void 0===e?"undefined":n()(e))?r=e:(10===(""+e).length&&(e=1e3*parseInt(e)),r=new Date(e));var o={y:r.getFullYear(),m:r.getMonth()+1,d:r.getDate(),h:r.getHours(),i:r.getMinutes(),s:r.getSeconds(),a:r.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=o[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}function u(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var a=new Date(t),r=new Date;r=r.valueOf();var n=Math.ceil((a-r)/864e5);return 0===n?"今天":n>0&&n<30?n+"天":n>=30&&n<360?Math.ceil(n/30)+"月":n>=360?Math.ceil(n/360)+"年":"逾期"+Math.ceil((r-a)/864e5)+"天"}}}]);