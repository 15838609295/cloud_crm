(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-1b04"],{"0GV7":function(e,t,r){"use strict";var a=r("zhyH");r.n(a).a},GmOA:function(e,t,r){"use strict";r.r(t);var a={data:function(){var e=this;return{imageUrl:"",dialogVisible:!1,dialogImageUrl:"",usersList:[],ruleList:[],form:{member_name:"",ach_state:0,after:0,after_type:4},formRules:{commission_before:[{validator:function(t,r,a){""===r&&2===e.form.after_type?a(new Error("请输入售前提成金额")):a()},trigger:"blur"}]}}},watch:{},created:function(){this.id=this.$route.query.id,this.getData(),this.getUsersList(),this.getRuleList()},methods:{getData:function(){var e=this,t="achievement/detail/"+this.id;this.$store.dispatch("GetConnect",{url:t}).then(function(t){var r=t.data;r.ach_state=0,r.after_type=4,r.after=0,r.sbr_id=null,r.pic_list=[],r.after_money=0,r.bonus_money=0;for(var a=0;a<t.data.sale_proof.length;a++)r.pic_list.push({url:e.imageUrl+t.data.sale_proof[a]});e.form=r,console.log(e.form)}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},getUsersList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"user/all-list"}).then(function(t){e.usersList=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},getRuleList:function(){var e=this,t={type:3===this.form.after_type?2:1};this.$store.dispatch("GetConnect",{url:"salerule/all-list",data:t}).then(function(t){e.ruleList=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},submitForm:function(e,t){var r=this;this.$refs[e].validate(function(e){if(e){var a=r.form;if(2===a.after_type&&(null===a.after_money||""===a.after_money))return void r.$message.error("请输入售后提成金额");if(2===a.after_type&&(null===a.bonus_money||""===a.bonus_money))return void r.$message.error("请输入售后提成金额");3!==a.after_type&&4!==a.after_type||(a.after_type=0);var l=t?"achievement/verify/"+r.id:"achievement/ajax/"+r.id;!t&&(a.action="refuse"),r.$store.dispatch("GetConnect",{url:l,data:a}).then(function(e){r.$message.success(e.msg),r.$router.back(-1)}).catch(function(e){r.$message.error(e.msg)})}else r.$message.error("提交失败,请检查必填项")})},resetForm:function(e){this.$refs[e].resetFields()},handlePictureCardPreview:function(e){this.dialogImageUrl=e.url,this.dialogVisible=!0}}},l=(r("0GV7"),r("KHd+")),o=Object(l.a)(a,function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",[r("el-card",{staticClass:"box-1"},[r("div",{attrs:{slot:"header"},slot:"header"},[r("span",[e._v("业绩订单-审核")])]),e._v(" "),r("el-form",{ref:"form",attrs:{model:e.form,rules:e.formRules,"label-position":"left","label-width":"150px"}},[r("el-row",{attrs:{type:"flex",justify:"space-around"}},[r("el-col",{attrs:{span:10}},[r("el-form-item",{attrs:{label:"客户名称"}},[e._v("\n            "+e._s(e.form.member_name)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"客户手机"}},[e._v("\n            "+e._s(e.form.member_phone)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"公司"}},[e._v("\n            "+e._s(e.form.company)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"职位"}},[e._v("\n            "+e._s(e.form.position)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"类型",prop:"type"}},[e._v("\n            "+e._s(0===e.form.type?"个人":"企业")+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"腾讯云ID"}},[e._v("\n            "+e._s(e.form.qq)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"微信"}},[e._v("\n            "+e._s(e.form.wechat)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"提交人"}},[e._v("\n            "+e._s(e.form.admin_name)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"商品名称"}},[e._v("\n            "+e._s(e.form.goods_name)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"商品金额"}},[e._v("\n            ￥"+e._s(e.form.goods_money)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"订单号"}},[e._v("\n            "+e._s(e.form.order_number)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"购买时间"}},[e._v("\n            "+e._s(e.form.buy_time)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"购买时长"}},[e._v("\n            "+e._s(e.form.buy_length)+"月\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"备注"}},[e._v("\n            "+e._s(e.form.remarks)+"\n          ")]),e._v(" "),r("el-form-item",{attrs:{label:"拒绝备注"}},[r("el-input",{attrs:{type:"textarea",rows:"4"},model:{value:e.form.refuse_remarks,callback:function(t){e.$set(e.form,"refuse_remarks",t)},expression:"form.refuse_remarks"}})],1)],1),e._v(" "),r("el-col",{staticClass:"line",attrs:{span:1}},[r("div")]),e._v(" "),r("el-col",{attrs:{span:10}},[r("el-form-item",{attrs:{label:"销售凭证",prop:"title"}},[e._l(e.form.pic_list,function(t,a){return r("img",{key:a,attrs:{src:t.url,alt:""},on:{click:function(r){e.handlePictureCardPreview(t)}}})}),e._v(" "),r("el-dialog",{attrs:{visible:e.dialogVisible,width:"80%"},on:{"update:visible":function(t){e.dialogVisible=t}}},[r("img",{staticStyle:{width:"100%"},attrs:{src:e.dialogImageUrl,alt:""}})])],2),e._v(" "),r("el-form-item",{attrs:{label:"是否参与业绩计算"}},[r("el-radio-group",{model:{value:e.form.ach_state,callback:function(t){e.$set(e.form,"ach_state",t)},expression:"form.ach_state"}},[r("el-radio",{attrs:{label:0}},[e._v("参与")]),e._v(" "),r("el-radio",{attrs:{label:1}},[e._v("不参与")])],1)],1),e._v(" "),r("el-form-item",{attrs:{label:"是否创建售后订单"}},[r("el-radio-group",{model:{value:e.form.after,callback:function(t){e.$set(e.form,"after",t)},expression:"form.after"}},[r("el-radio",{attrs:{label:0}},[e._v("创建")]),e._v(" "),r("el-radio",{attrs:{label:1}},[e._v("不创建")])],1)],1),e._v(" "),r("el-form-item",{attrs:{label:"售后人员"}},[r("el-select",{attrs:{placeholder:"请选择售后人员",filterable:""},model:{value:e.form.after_sale_id,callback:function(t){e.$set(e.form,"after_sale_id",t)},expression:"form.after_sale_id"}},e._l(e.usersList,function(e){return r("el-option",{key:e.id,attrs:{label:e.name,value:e.id}})}))],1),e._v(" "),r("el-form-item",{attrs:{label:"是否有提成"}},[r("el-radio-group",{on:{change:e.getRuleList},model:{value:e.form.after_type,callback:function(t){e.$set(e.form,"after_type",t)},expression:"form.after_type"}},[r("el-radio",{attrs:{label:4}},[e._v("比例规则")]),e._v(" "),r("el-radio",{attrs:{label:3}},[e._v("固定规则")]),e._v(" "),r("el-radio",{attrs:{label:2}},[e._v("手动输入")]),e._v(" "),r("el-radio",{attrs:{label:1}},[e._v("无提成")])],1)],1),e._v(" "),e.form.after_type>2?r("el-form-item",{attrs:{label:"提成规则"}},[r("el-select",{attrs:{placeholder:"请选择提成规则"},model:{value:e.form.sbr_id,callback:function(t){e.$set(e.form,"sbr_id",t)},expression:"form.sbr_id"}},e._l(e.ruleList,function(e){return r("el-option",{key:e.id,attrs:{label:e.rule_name,value:e.id}})}))],1):e._e(),e._v(" "),2===e.form.after_type?r("el-form-item",{attrs:{label:"售前提成金额"}},[r("el-input",{model:{value:e.form.order_bonus,callback:function(t){e.$set(e.form,"order_bonus",t)},expression:"form.order_bonus"}})],1):e._e(),e._v(" "),2===e.form.after_type?r("el-form-item",{attrs:{label:"售后提成金额"}},[r("el-input",{model:{value:e.form.after_money,callback:function(t){e.$set(e.form,"after_money",t)},expression:"form.after_money"}})],1):e._e(),e._v(" "),2===e.form.after_type?r("el-form-item",{attrs:{label:"奖金金额"}},[r("el-input",{model:{value:e.form.bonus_money,callback:function(t){e.$set(e.form,"bonus_money",t)},expression:"form.bonus_money"}})],1):e._e()],1)],1),e._v(" "),r("el-row",{attrs:{type:"flex",justify:"center"}},[r("el-button",{staticClass:"do-btn",attrs:{type:"success"},on:{click:function(t){e.submitForm("form",!0)}}},[e._v("同 意")]),e._v(" "),r("el-button",{attrs:{type:"danger"},on:{click:function(t){e.submitForm("form",!1)}}},[e._v("拒 绝")])],1)],1)],1)],1)},[],!1,null,"1a8cf348",null);o.options.__file="review.vue";t.default=o.exports},zhyH:function(e,t,r){}}]);