(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-3932"],{BBsh:function(t,e,r){},JS0Y:function(t,e,r){"use strict";r.r(e);var a=r("P2sY"),s=r.n(a),n={data:function(){return{title:"",form:{cover:""}}},watch:{},created:function(){this.id=this.$route.query.id,this.getData()},methods:{getData:function(){var t=this,e="exam/examInfo/"+this.id;this.$store.dispatch("GetConnect",{url:e}).then(function(e){s()(t.form,e.data),t.form.group_name=t.form.group_name.split("/")}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员")})},toAnswerSheet:function(){this.$router.push({path:"/exam/answerSheet",query:{id:this.id}})}}},o=(r("SmOh"),r("KHd+")),i=Object(o.a)(n,function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("div",{staticClass:"app-container bg-gray"},[r("el-card",{staticClass:"box-1"},[r("div",{staticClass:"header",attrs:{slot:"header"},slot:"header"},[r("span",[t._v("考试信息")])]),t._v(" "),r("el-form",{attrs:{model:t.form,"label-width":"160px"}},[r("el-form-item",{attrs:{label:"封面图片"}},[r("ws-show-img",{staticStyle:{width:"188px",height:"100px"},attrs:{src:t.form.cover}})],1),t._v(" "),r("el-form-item",{attrs:{label:"考试名称"}},[t._v("\n        "+t._s(t.form.name)+"\n      ")]),t._v(" "),r("el-form-item",{attrs:{label:"试卷名称"}},[t._v("\n        "+t._s(t.form.test_paper_name)+"\n      ")]),t._v(" "),r("el-form-item",{attrs:{label:"考试时间"}},[t._v("\n        "+t._s(t.form.start_time+"~"+t.form.end_time)+"\n      ")]),t._v(" "),r("el-form-item",{attrs:{label:"合格分数"}},[t._v("\n        "+t._s(t.form.qualified_score)+"\n      ")]),t._v(" "),r("el-form-item",{attrs:{label:"考试时长"}},[t._v("\n        "+t._s(t.form.limit_time)+"分钟\n      ")]),t._v(" "),r("el-form-item",{attrs:{label:"可考次数"}},[t._v("\n        "+t._s(t.form.number)+"\n      ")]),t._v(" "),r("el-form-item",{attrs:{label:"考试对象"}},t._l(t.form.group_name,function(e){return r("el-tag",{key:e,staticStyle:{margin:"2px"},attrs:{type:"warning"}},[t._v(t._s(e))])})),t._v(" "),r("el-form-item",{attrs:{label:"排名显示"}},[t._v("\n        "+t._s(1===t.form.is_ranking?"是":"否")+"\n      ")]),t._v(" "),r("el-form-item",{attrs:{label:"是否打乱顺序"}},[t._v("\n        "+t._s(t.form.is_sort?"是":"否")+"\n      ")]),t._v(" "),r("el-form-item",{attrs:{label:"是否允许复制"}},[t._v("\n        "+t._s(t.form.is_copy?"不允许":"允许")+"\n      ")]),t._v(" "),r("el-form-item",{attrs:{label:"通知方式"}},[t._v("\n        "+t._s(1===t.form.notice?"短信":2===t.form.notice?"微信":"短信&微信")+"\n      ")]),t._v(" "),r("el-form-item",{attrs:{label:"考试说明"}},[r("div",{domProps:{innerHTML:t._s(t.form.explain)}},[t._v(t._s(t.form.explain))])]),t._v(" "),r("el-form-item",{attrs:{label:"考试状态"}},[t._v("\n        "+t._s(t.form.status_txt)+"\n      ")]),t._v(" "),r("div",{staticStyle:{"text-align":"center"}},[t.queryMatch(190)?r("el-button",{staticClass:"bg-green",attrs:{type:"primary"},on:{click:t.toAnswerSheet}},[t._v("查看统计分析")]):t._e(),t._v(" "),r("el-button",{attrs:{type:"info"},on:{click:function(e){t.$router.back(-1)}}},[t._v("返回")])],1)],1)],1)],1)},[],!1,null,"c3243e0c",null);i.options.__file="info.vue";e.default=i.exports},SmOh:function(t,e,r){"use strict";var a=r("BBsh");r.n(a).a}}]);