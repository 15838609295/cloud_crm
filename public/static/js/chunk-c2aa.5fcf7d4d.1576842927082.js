(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-c2aa"],{Iixg:function(e,t,o){"use strict";var r=o("p0cj");o.n(r).a},ntKH:function(e,t,o){"use strict";o.r(t);var r=o("gDS+"),n=o.n(r),a=o("P2sY"),s=o.n(a),l={data:function(){return{answerIndex:["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"],form:{id:"",itemId:"",name:"",annex:"",type:1,must:1,option:[{label:"A",value:""}],answer:0,answer2:[],fraction:"",remarks:""},formRules:{name:[{required:!0,message:"请输入客户名",trigger:"change"}]}}},watch:{},created:function(){this.form.id=this.$route.query.id,this.form.itemId=this.$route.query.itemId,this.getQuestionInfo()},methods:{getQuestionInfo:function(){var e=this,t="exam/itemInfo/"+this.form.itemId;this.$store.dispatch("GetConnect",{url:t}).then(function(t){s()(e.form,t.data),1===e.form.type?e.form.answer=e.form.answer[0]:2===e.form.type&&(e.form.answer2=e.form.answer),e.form.option=e.form.option||[],e.form.id=e.$route.query.id,e.form.itemId=e.$route.query.itemId}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},removeItem:function(e,t){var o=this.form[e].indexOf(t);0!==o&&this.form[e].splice(o,1)},addItem:function(e){this.form[e].push({label:"",value:""})},submitForm:function(e){var t=this;this.$refs[e].validate(function(e){if(e){var o=s()({},t.form);for(var r in o.option)o.option[r].label=t.answerIndex[r];2===o.type&&(o.answer=o.answer2.join(",")),o.option=n()(o.option);var a=o.id?"exam/updateTestItem":"exam/updateItem";t.$store.dispatch("GetConnect",{url:a,data:o}).then(function(e){t.$message.success(e.msg),t.$router.back(-1)}).catch(function(e){t.$message.error(e.msg)})}else t.$message.error("提交失败,请检查必填项")})}}},i=(o("Iixg"),o("KHd+")),m=Object(i.a)(l,function(){var e=this,t=e.$createElement,o=e._self._c||t;return o("div",{staticClass:"app-container bg-gray"},[o("el-card",{staticClass:"box-1"},[o("div",{attrs:{slot:"header"},slot:"header"},[o("span",[e._v("编辑題目")])]),e._v(" "),o("el-form",{ref:"form",attrs:{model:e.form,rules:e.formRules,"label-width":"160px"}},[o("el-form-item",{attrs:{label:"題目标题"}},[o("el-input",{model:{value:e.form.name,callback:function(t){e.$set(e.form,"name",t)},expression:"form.name"}})],1),e._v(" "),o("el-form-item",{attrs:{label:"附图"}},[o("ws-upload-single-img",{attrs:{path:e.form.annex},model:{value:e.form.annex,callback:function(t){e.$set(e.form,"annex",t)},expression:"form['annex']"}})],1),e._v(" "),o("el-form-item",{attrs:{label:"必填"}},[o("el-radio-group",{model:{value:e.form.must,callback:function(t){e.$set(e.form,"must",t)},expression:"form.must"}},[o("el-radio",{attrs:{label:1}},[e._v("是")]),e._v(" "),o("el-radio",{attrs:{label:0}},[e._v("否")])],1)],1),e._v(" "),o("el-form-item",{attrs:{label:"题目类型"}},[o("el-radio-group",{model:{value:e.form.type,callback:function(t){e.$set(e.form,"type",t)},expression:"form.type"}},[o("el-radio",{attrs:{label:1}},[e._v("单选题")]),e._v(" "),o("el-radio",{attrs:{label:2}},[e._v("多选题")]),e._v(" "),o("el-radio",{attrs:{label:3}},[e._v("填空题")])],1)],1),e._v(" "),3!==e.form.type?o("el-form-item",{attrs:{label:"题目选项"}},[e._l(e.form.option,function(t,r){return o("el-input",{key:r,attrs:{placeholder:"请输入选项内容"},model:{value:e.form.option[r].value,callback:function(t){e.$set(e.form.option[r],"value",t)},expression:"form['option'][questionAnswerIndex]['value']"}},[o("template",{slot:"prepend"},[e._v(e._s(e.answerIndex[r]))]),e._v(" "),o("el-button",{attrs:{slot:"append",icon:"el-icon-delete"},on:{click:function(o){o.preventDefault(),e.removeItem("option",t)}},slot:"append"})],2)}),e._v(" "),o("el-button",{staticStyle:{"margin-top":"5px"},attrs:{type:"info",size:"small",plain:""},on:{click:function(t){t.preventDefault(),e.addItem("option")}}},[e._v("新增选项")])],2):e._e(),e._v(" "),o("el-form-item",{attrs:{label:"正确答案"}},[1===e.form.type?o("el-radio-group",{model:{value:e.form.answer,callback:function(t){e.$set(e.form,"answer",t)},expression:"form.answer"}},e._l(e.form.option,function(t,r){return o("el-radio",{key:r,attrs:{label:r}},[e._v("\n            "+e._s(e.answerIndex[r])+"\n          ")])})):2===e.form.type?o("el-checkbox-group",{model:{value:e.form.answer2,callback:function(t){e.$set(e.form,"answer2",t)},expression:"form.answer2"}},e._l(e.form.option,function(t,r){return o("el-checkbox",{key:r,attrs:{label:r}},[e._v("\n            "+e._s(e.answerIndex[r])+"\n          ")])})):o("el-input",{model:{value:e.form.answer,callback:function(t){e.$set(e.form,"answer",t)},expression:"form.answer"}})],1),e._v(" "),o("el-form-item",{attrs:{label:"题目分数"}},[o("el-input-number",{model:{value:e.form.fraction,callback:function(t){e.$set(e.form,"fraction",t)},expression:"form['fraction']"}})],1),e._v(" "),o("el-form-item",{attrs:{label:"题目分析"}},[o("ws-editor",{model:{value:e.form.remarks,callback:function(t){e.$set(e.form,"remarks",t)},expression:"form['remarks']"}})],1),e._v(" "),o("el-form-item",[o("el-button",{staticClass:"bg-green btn",attrs:{type:"success"},on:{click:function(t){e.submitForm("form")}}},[e._v("保存")]),e._v(" "),o("el-button",{staticClass:"btn",attrs:{type:"info"},on:{click:function(t){e.$router.back(-1)}}},[e._v("取消")])],1)],1)],1)],1)},[],!1,null,"6f8373b6",null);m.options.__file="modify.vue";t.default=m.exports},p0cj:function(e,t,o){}}]);