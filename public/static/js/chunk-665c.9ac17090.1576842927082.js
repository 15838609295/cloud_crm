(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-665c"],{NMZI:function(e,t,r){},Rxl1:function(e,t,r){"use strict";var a=r("NMZI");r.n(a).a},m3v3:function(e,t,r){"use strict";r.r(t);var a=r("zAZ/"),l={data:function(){return{searchTerms:{searchValue:""},dialogVisible:!1,ruleForm:{rule_name:"",rule_type:0,type:0,cost:"",pre_bonus:"",after_bonus:"",after_first_bonus:"",bonus:0,first_bonus:0},ruleFormRules:{rule_name:[{required:!0,message:"请输入规则名称",trigger:"blur"}],rule_type:[{required:!0,message:"请选择规则类型",trigger:"blur"}],cost:[{required:!0,message:"请输入成本",trigger:"blur"}],pre_bonus:[{required:!0,message:"请输入销售业绩提成",trigger:"blur"}],after_bonus:[{required:!0,message:"请输入售后提成",trigger:"blur"}],after_first_bonus:[{required:!0,message:"请输入售后第一次提成",trigger:"blur"}]},tableMaxHeight:document.documentElement.clientHeight-280,tableLoading:!1,currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,pageSizes:[10,20,50,100,1e3],total:0,tableData:[]}},watch:{},created:function(){this.getData()},methods:{handleSortChange:a.e,handleSizeChange:a.d,handleCurrentChange:a.c,getData:function(e){var t=this;this.tableLoading=!0,e||(e={});var r={page_no:this.currentPage,page_size:this.pageSize,search:this.searchTerms.searchValue};e.sortName&&(r.sortName=e.sortName),e.sortOrder&&(r.sortOrder=e.sortOrder),this.$store.dispatch("GetConnect",{url:"salerule/list",data:r}).then(function(e){t.tableData=e.data.rows,t.total=e.data.total,t.tableLoading=!1}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},showRuleDialog:function(e){e&&(this.ruleForm=e),this.ruleForm.title=e?"编辑提成规则":"创建提成规则",this.dialogVisible=!0},submitForm:function(e){var t=this;this.$refs[e].validate(function(e){if(e){var r=t.ruleForm,a=t.ruleForm.id?"salerule/edit/"+t.ruleForm.id:"salerule/create";t.$store.dispatch("GetConnect",{url:a,data:r}).then(function(e){t.$message.success(e.msg),t.dialogVisible=!1,t.getData()}).catch(function(e){t.$message.error(e.original.msg)})}else t.$message.error("提交失败,请检查必填项")})},toDelete:function(e){var t=this;this.$confirm("此操作将删除该规则, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var r="salerule/delete/"+e.id;t.$store.dispatch("GetConnect",{url:r}).then(function(e){t.$message.success(e.msg),t.getData()}).catch(function(e){t.$message.error(e.msg)})})},toModifyRoles:function(e){this.isShow=!1,this.$router.push({path:"/permissions/roles/modifyRole",query:{title:e}})},changeStatus:function(e){var t=this;this.$confirm("此操作将"+(e.status?"禁用":"启用")+"该规则, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var r={id:e.id,status:!e.status};t.$store.dispatch("GetConnect",{url:"salerule/updateSaleRule",data:r}).then(function(e){t.$message.success(e.msg),t.getData()}).catch(function(e){t.$message.error(e.msg)})})}}},s=(r("Rxl1"),r("KHd+")),n=Object(s.a)(l,function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",{staticClass:"app-container  bg-gray"},[r("el-row",{staticClass:"box-1"},[r("el-col",{attrs:{span:24}},[r("el-card",[r("div",{staticClass:"header"},[r("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-circle-plus"},on:{click:function(t){e.showRuleDialog()}}},[e._v("添加规则")]),e._v(" "),r("div",[r("el-input",{staticClass:"search-input",attrs:{placeholder:"请输入内容"},on:{change:e.getData},model:{value:e.searchTerms.searchValue,callback:function(t){e.$set(e.searchTerms,"searchValue",t)},expression:"searchTerms.searchValue"}}),e._v(" "),r("el-button",{staticClass:"bg-green",attrs:{type:"success",icon:"el-icon-search"},on:{click:e.getData}},[e._v("搜索")])],1)],1),e._v(" "),r("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:e.tableData,"max-height":e.tableMaxHeight,border:"","highlight-current-row":""},on:{"sort-change":e.handleSortChange}},[r("el-table-column",{attrs:{prop:"id",label:"ID",width:"100","header-align":"center",align:"center",sortable:""}}),e._v(" "),r("el-table-column",{attrs:{prop:"rule_name",label:"规则名称","min-width":"200","header-align":"center",align:"center"}}),e._v(" "),r("el-table-column",{attrs:{prop:"rule_type",label:"提成类型","min-width":"140","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(1===t.row.type?"售后费用":"奖金")+"\n            ")]}}])}),e._v(" "),r("el-table-column",{attrs:{prop:"rule_type",label:"发放模式","min-width":"140","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(0===t.row.rule_type?"按比例":"固定金额")+"\n            ")]}}])}),e._v(" "),r("el-table-column",{attrs:{prop:"created_at",label:"创建时间","min-width":"160","header-align":"center",align:"center",sortable:""}}),e._v(" "),r("el-table-column",{attrs:{prop:"rule_type",label:"状态","min-width":"140","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[r("div",{on:{click:function(r){e.changeStatus(t.row)}}},[r("el-tag",{staticClass:"cursor-p",attrs:{type:t.row.status?"success":"danger"}},[e._v(e._s(t.row.status?"已启用":"已禁用")+"\n                ")])],1)]}}])}),e._v(" "),r("el-table-column",{attrs:{label:"操作",width:"200","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[r("el-button",{staticClass:"bg-green",attrs:{type:"success",size:"small",icon:"el-icon-edit",circle:""},on:{click:function(r){e.showRuleDialog(t.row)}}}),e._v(" "),r("el-button",{attrs:{type:"danger",size:"small",icon:"el-icon-delete",circle:""},on:{click:function(r){e.toDelete(t.row)}}})]}}])})],1),e._v(" "),r("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1)],1)],1),e._v(" "),r("el-dialog",{staticClass:"dialog",attrs:{visible:e.dialogVisible,title:e.ruleForm.title,width:"560px",center:""},on:{"update:visible":function(t){e.dialogVisible=t},close:function(t){e.ruleForm={}}}},[r("el-form",{ref:"ruleForm",attrs:{model:e.ruleForm,rules:e.ruleFormRules,"label-width":"150px"}},[r("el-form-item",{attrs:{label:"规则名称",prop:"rule_name"}},[r("el-input",{model:{value:e.ruleForm.rule_name,callback:function(t){e.$set(e.ruleForm,"rule_name",t)},expression:"ruleForm.rule_name"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"提成类型",prop:"rule_type"}},[r("el-radio-group",{attrs:{disabled:"编辑提成规则"===e.ruleForm.title},model:{value:e.ruleForm.type,callback:function(t){e.$set(e.ruleForm,"type",t)},expression:"ruleForm['type']"}},[r("el-radio",{attrs:{label:0}},[e._v("奖金")]),e._v(" "),r("el-radio",{attrs:{label:1}},[e._v("售后提成")])],1)],1),e._v(" "),r("el-form-item",{attrs:{label:"提成模式",prop:"rule_type"}},[r("el-radio-group",{attrs:{disabled:"编辑提成规则"===e.ruleForm.title},model:{value:e.ruleForm.rule_type,callback:function(t){e.$set(e.ruleForm,"rule_type",t)},expression:"ruleForm['rule_type']"}},[r("el-radio",{attrs:{label:0}},[e._v("按比例")]),e._v(" "),r("el-radio",{attrs:{label:1}},[e._v("固定金额")])],1),e._v(" "),r("div",{staticClass:"remark"},[e._v("*注:提成模式保存后不可更改")])],1),e._v(" "),0===e.ruleForm.rule_type?r("el-form-item",{attrs:{label:"成本",prop:"cost"}},[r("el-input",{attrs:{type:"number"},model:{value:e.ruleForm.cost,callback:function(t){e.$set(e.ruleForm,"cost",t)},expression:"ruleForm.cost"}},[r("template",{slot:"append"},[e._v("%")])],2),e._v(" "),r("div",{staticClass:"remark"},[e._v("*注:成本是按百分比计算")])],1):e._e(),e._v(" "),r("el-form-item",{attrs:{label:"销售业绩提成",prop:"pre_bonus"}},[r("el-input",{attrs:{type:"number"},model:{value:e.ruleForm.pre_bonus,callback:function(t){e.$set(e.ruleForm,"pre_bonus",t)},expression:"ruleForm.pre_bonus"}},[e.ruleForm.rule_type?e._e():r("template",{slot:"append"},[e._v("%")])],2)],1),e._v(" "),e.ruleForm.type?r("el-form-item",{attrs:{label:"售后提成"}},[r("el-input",{attrs:{type:"number"},model:{value:e.ruleForm.after_bonus,callback:function(t){e.$set(e.ruleForm,"after_bonus",t)},expression:"ruleForm.after_bonus"}},[e.ruleForm.rule_type?e._e():r("template",{slot:"append"},[e._v("%")])],2)],1):e._e(),e._v(" "),e.ruleForm.type&&!e.ruleForm.rule_type?r("el-form-item",{attrs:{label:"售后第一次提成"}},[r("el-input",{attrs:{type:"number"},model:{value:e.ruleForm.after_first_bonus,callback:function(t){e.$set(e.ruleForm,"after_first_bonus",t)},expression:"ruleForm.after_first_bonus"}},[r("template",{slot:"append"},[e._v("%")])],2),e._v(" "),r("div",{staticClass:"remark"},[e._v("* 注：填0则为平均分配")])],1):e._e(),e._v(" "),e.ruleForm.type?e._e():r("el-form-item",{attrs:{label:"奖金"}},[r("el-input",{attrs:{type:"number"},model:{value:e.ruleForm.bonus,callback:function(t){e.$set(e.ruleForm,"bonus",t)},expression:"ruleForm['bonus']"}},[e.ruleForm.rule_type?e._e():r("template",{slot:"append"},[e._v("%")])],2)],1),e._v(" "),e.ruleForm.type||e.ruleForm.rule_type?e._e():r("el-form-item",{attrs:{label:"奖金第一次分配"}},[r("el-input",{attrs:{type:"number"},model:{value:e.ruleForm.first_bonus,callback:function(t){e.$set(e.ruleForm,"first_bonus",t)},expression:"ruleForm['first_bonus']"}},[r("template",{slot:"append"},[e._v("%")])],2),e._v(" "),r("div",{staticClass:"remark"},[e._v("* 注：填0则为平均分配")])],1)],1),e._v(" "),r("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[r("el-button",{on:{click:function(t){e.dialogVisible=!1}}},[e._v("取 消")]),e._v(" "),r("el-button",{attrs:{type:"primary"},on:{click:function(t){e.submitForm("ruleForm")}}},[e._v("确 定")])],1)],1)],1)},[],!1,null,"1063b786",null);n.options.__file="commission.vue";t.default=n.exports},"zAZ/":function(e,t,r){"use strict";r.d(t,"e",function(){return s}),r.d(t,"c",function(){return n}),r.d(t,"d",function(){return o}),r.d(t,"a",function(){return i}),r.d(t,"f",function(){return u}),r.d(t,"b",function(){return c});var a=r("EJiy"),l=r.n(a);function s(e){var t=e.column,r=e.prop,a=e.order;this.order={column:t,prop:r,order:a},this.getData({sortName:r,sortOrder:a&&("ascending"===a?"asc":"desc")})}function n(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function o(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function i(e){this.th[e].check=!this.th[e].check}function u(e,t){if(0===arguments.length)return null;var r=t||"{y}-{m}-{d} {h}:{i}:{s}",a=void 0;"object"===(void 0===e?"undefined":l()(e))?a=e:(10===(""+e).length&&(e=1e3*parseInt(e)),a=new Date(e));var s={y:a.getFullYear(),m:a.getMonth()+1,d:a.getDate(),h:a.getHours(),i:a.getMinutes(),s:a.getSeconds(),a:a.getDay()};return r.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var r=s[t];return"a"===t?["日","一","二","三","四","五","六"][r]:(e.length>0&&r<10&&(r="0"+r),r||0)})}function c(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var r=new Date(t),a=new Date;a=a.valueOf();var l=Math.ceil((r-a)/864e5);return 0===l?"今天":l>0&&l<30?l+"天":l>=30&&l<360?Math.ceil(l/30)+"月":l>=360?Math.ceil(l/360)+"年":"逾期"+Math.ceil((a-r)/864e5)+"天"}}}]);