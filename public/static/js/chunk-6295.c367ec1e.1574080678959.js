(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-6295"],{"7LxZ":function(t,e,i){"use strict";i.r(e);var n={data:function(){return{datePickerOptions:{shortcuts:[{text:"1天",onClick:function(t){var e=new Date;e.setTime(e.getTime()+864e5),t.$emit("pick",e)}},{text:"3天",onClick:function(t){var e=new Date;e.setTime(e.getTime()+2592e5),t.$emit("pick",e)}},{text:"一周",onClick:function(t){var e=new Date;e.setTime(e.getTime()+6048e5),t.$emit("pick",e)}},{text:"两周",onClick:function(t){var e=new Date;e.setTime(e.getTime()+12096e5),t.$emit("pick",e)}},{text:"一个月",onClick:function(t){var e=new Date;e.setTime(e.getTime()+2592e6),t.$emit("pick",e)}},{text:"三个月",onClick:function(t){var e=new Date;e.setTime(e.getTime()+7776e6),t.$emit("pick",e)}}]},progressList:["初步沟通","需求分析","展示引导","方案报价","协商议价","成单签约","丢单"],info:{contact_time:"",next_contact_time:""}}},watch:{},created:function(){this.title=this.$route.query.title,this.id=this.$route.query.id,this.getCustomerInfo(this.$route.query.id)},methods:{parseTime:i("zAZ/").f,getCustomerInfo:function(t){var e=this,i="customer/detail/"+t;this.$store.dispatch("GetConnect",{url:i}).then(function(t){e.info=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},submit:function(){var t=this;if(this.info.next_contact_time&&""!==this.info.next_contact_time&&null!==this.info.next_contact_time){var e={action:"submit_log",progress:this.info.progress,contact_time:this.parseTime(new Date),next_contact_time:this.parseTime(this.info.next_contact_time),content:this.info.content},i="customer/ajax/"+this.info.id;this.$store.dispatch("GetConnect",{url:i,data:e}).then(function(e){t.$message.success("沟通记录添加成功"),t.$router.back(-1)}).catch(function(e){t.$message.error(e.msg)})}else this.$message.error("检查时间是否选择")},cancel:function(){this.$router.go(-1)}}},s=(i("BG6j"),i("KHd+")),o=Object(s.a)(n,function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",[i("el-card",{staticStyle:{"max-width":"1000px",margin:"0 auto"}},[i("div",{staticClass:"box-2"},[i("h4",[t._v("● 指派记录")]),t._v(" "),i("el-card",[i("el-row",t._l(t.info.assign_log,function(e,n){return i("el-col",{key:n,attrs:{xs:12,sm:8,md:7,lg:6,xl:4}},[i("h1",[t._v(t._s(e.name))]),t._v(" "),i("span",[t._v(t._s(e.contact_time))])])}))],1),t._v(" "),i("div",{staticStyle:{height:"36px"}})],1),t._v(" "),i("div",{staticClass:"box-1"},[i("h4",[t._v("客户信息")]),t._v(" "),i("div",[i("div",{staticClass:"text-157a"},[t._v("名称：")]),t._v(" "),i("div",{staticClass:"text-1543 margin-l-10"},[t._v(t._s(t.info.name))])]),t._v(" "),i("div",[i("div",[t._v("电话：")]),t._v(" "),i("div",[t._v(t._s(t.info.mobile))])]),t._v(" "),i("div",[i("div",[t._v("备注：")]),t._v(" "),i("div",[t._v(t._s(t.info.remarks))])])]),t._v(" "),i("div",{staticClass:"box-2"},[i("h4",[t._v("● 沟通记录")]),t._v(" "),i("div",{staticStyle:{"margin-bottom":"15px",color:"#1EC2A0"}},[t._v("下次联系时间："+t._s(t.info.contact_next_time))]),t._v(" "),t.info.contact_record&&0!==t.info.contact_record.length?t._e():i("el-card",[i("div",[t._v("空空如也~")])]),t._v(" "),t._l(t.info.contact_record,function(e,n){return i("div",{key:n,staticClass:"timeline"},[i("el-row",{staticClass:"content"},[i("el-card",[i("div",{staticClass:"header",attrs:{slot:"header"},slot:"header"},[i("span",[t._v(t._s(e.adminname))]),t._v(" "),i("div",{staticClass:"text-right",staticStyle:{color:"#b7b7b7"}},[t._v(t._s(e.comm_time))])]),t._v(" "),i("div",{staticClass:"body",domProps:{innerHTML:t._s(e.contentlog)}},[t._v(t._s(e.contentlog))])])],1)],1)}),t._v(" "),i("div",{staticStyle:{height:"36px"}})],2),t._v(" "),i("div",{staticClass:"box-1"},[i("h4",[t._v("添加新记录")]),t._v(" "),i("div",{staticClass:"radio"},[i("div",[t._v("客户跟进状态：")]),t._v(" "),i("el-radio-group",{model:{value:t.info.progress,callback:function(e){t.$set(t.info,"progress",e)},expression:"info.progress"}},t._l(t.progressList,function(e){return i("el-radio",{key:e,attrs:{label:e}},[t._v(t._s(e))])}))],1),t._v(" "),i("div",[i("div",[t._v("下次联系：")]),t._v(" "),i("el-date-picker",{attrs:{"picker-options":t.datePickerOptions,type:"datetime",placeholder:"选择日期时间",align:"right"},model:{value:t.info.next_contact_time,callback:function(e){t.$set(t.info,"next_contact_time",e)},expression:"info.next_contact_time"}}),t._v(" "),i("div",{staticClass:"remark"},[t._v("      *注:请选择10年以内日期")])],1),t._v(" "),i("div",[i("div",[t._v("沟通内容：")]),t._v(" "),i("el-input",{attrs:{type:"textarea",rows:"4"},model:{value:t.info.content,callback:function(e){t.$set(t.info,"content",e)},expression:"info.content"}})],1),t._v(" "),i("div",[i("el-button",{staticClass:"bg-green",attrs:{type:"success"},on:{click:t.submit}},[t._v("确 定")])],1)])])],1)},[],!1,null,"7d31b964",null);o.options.__file="record.vue";e.default=o.exports},BG6j:function(t,e,i){"use strict";var n=i("sxP+");i.n(n).a},"sxP+":function(t,e,i){},"zAZ/":function(t,e,i){"use strict";i.d(e,"e",function(){return o}),i.d(e,"c",function(){return r}),i.d(e,"d",function(){return a}),i.d(e,"a",function(){return c}),i.d(e,"f",function(){return d}),i.d(e,"b",function(){return l});var n=i("EJiy"),s=i.n(n);function o(t){var e=t.column,i=t.prop,n=t.order;this.order={column:e,prop:i,order:n},this.getData({sortName:i,sortOrder:n&&("ascending"===n?"asc":"desc")})}function r(t){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function a(t){this.$store.getters.userInfo.pageSize=t,this.pageSize=t,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function c(t){this.th[t].check=!this.th[t].check}function d(t,e){if(0===arguments.length)return null;var i=e||"{y}-{m}-{d} {h}:{i}:{s}",n=void 0;"object"===(void 0===t?"undefined":s()(t))?n=t:(10===(""+t).length&&(t=1e3*parseInt(t)),n=new Date(t));var o={y:n.getFullYear(),m:n.getMonth()+1,d:n.getDate(),h:n.getHours(),i:n.getMinutes(),s:n.getSeconds(),a:n.getDay()};return i.replace(/{(y|m|d|h|i|s|a)+}/g,function(t,e){var i=o[e];return"a"===e?["日","一","二","三","四","五","六"][i]:(t.length>0&&i<10&&(i="0"+i),i||0)})}function l(t){var e=t.substr(0,10);e=e.replace(/-/g,"/");var i=new Date(e),n=new Date;n=n.valueOf();var s=Math.ceil((i-n)/864e5);return 0===s?"今天":s>0&&s<30?s+"天":s>=30&&s<360?Math.ceil(s/30)+"月":s>=360?Math.ceil(s/360)+"年":"逾期"+Math.ceil((n-i)/864e5)+"天"}}}]);