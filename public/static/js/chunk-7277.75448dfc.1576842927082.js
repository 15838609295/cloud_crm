(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-7277"],{"0Ruj":function(e,t,a){"use strict";var r=a("W3Ij");a.n(r).a},HuyM:function(e,t,a){},W3Ij:function(e,t,a){},Yfch:function(e,t,a){"use strict";function r(e){return/^(https?|ftp):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/.test(e)}function o(e){return/^[A-Za-z0-9\u4e00-\u9fa5]+$/.test(e)&&!/^\d+$/.test(e)}function n(e){return/^[A-Za-z0-9\u4e00-\u9fa5]+$/.test(e)}function s(e,t){for(var a=e.split(""),r=0;r<a.length;r++)r>t-1&&r<a.length-t&&(a[r]="*");return a.join("")}a.d(t,"d",function(){return r}),a.d(t,"c",function(){return o}),a.d(t,"b",function(){return n}),a.d(t,"a",function(){return s})},crBu:function(e,t,a){"use strict";a.r(t);var r=a("P2sY"),o=a.n(r),n={data:function(){return{searchValue:"",ImageUrl:"",currentPage:1,pageSize:this.$store.getters.userInfo.pageSize,total:30,id:"",baseForm:{title:"",seo_title:"",site_status:1,qy_redirect:"",agent_url:"",shortcut:"0",shortcut_name:"",shortcut_url:"",avatar_status:0},baseFormRules:{title:[{required:!0,message:"请输入站点名称",trigger:"blur"}]},qywxForm:{company_id:"",tongxl_secret:"",qywxLogin:0,push_secret:"",qy_mch_id:"",qy_pay_secret:"",qy_appid:"",qy_wx_pay_key:""},qywxFormRules:{},adminWxAppForm:{wechat_appid:"",wechat_secret:"",wechat_name:"",admin_wechat_qr:"",qy_mch_id:"",qy_pay_secret:"",admin_apiclient_cert:"",admin_apiclient_key:""},adminWxAppFormRules:{},agentWxAppForm:{member_wechat_appid:"",member_wechat_secret:"",wxapplet_name:"",member_wechat_qr:"",wx_pay_merchant_id:"",wx_pay_secret_key:"",member_apiclient_cert:"",member_apiclient_key:""},agentWxAppFormRules:{},wxPayForm:{qy_mch_id:"",qy_pay_secret:"",apiclient_cert:"",apiclient_key:""},wxPayFormRules:{},bonusForm:{bonus_alone:"",bonus_proportion:"",bonus_today_second:"",bonus_month_second:"",bonus_small:"",bonus_explain:""},bonusFormRules:{},capitalForm:{royalty_alone:"",royalty_proportion:"",royalty_today_second:"",royalty_month_second:"",royalty_small:""},capitalFormRules:{},userAgreementForm:{agreementTitle:"",agreementContent:""},userAgreementFormRules:{agreementTitle:[{required:!0,message:"请输入用户协议名称",trigger:"blur"}],agreementContent:[{required:!0,message:"请输入用户协议内容",trigger:"blur"}]},privacyAgreementForm:{privacyTitle:"",privacyContent:""},privacyAgreementFormRules:{privacyTitle:[{required:!0,message:"请输入隐私协议名称",trigger:"blur"}],privacyContent:[{required:!0,message:"请输入隐私协议内容",trigger:"blur"}]}}},created:function(){this.getSiteConfig()},methods:{encryptionString:a("Yfch").a,getSiteConfig:function(){var e=this;this.$store.dispatch("GetConnect",{url:"siteconfig/list"}).then(function(t){e.id=t.data.id,e.baseForm={title:t.data.title,seo_title:t.data.seo_title,site_status:0!==t.data.site_status,qy_redirect:t.data.qy_redirect,agent_url:t.data.agent_url,shortcut:t.data.shortcut,shortcut_name:t.data.shortcut_name,shortcut_url:t.data.shortcut_url,avatar_status:t.data.avatar_status},e.qywxForm={company_id:t.data.company_id,tongxl_secret:t.data.tongxl_secret,qywxLogin:t.data.qywxLogin,push_secret:t.data.push_secret,qy_mch_id:t.data.qy_mch_id,qy_pay_secret:t.data.qy_pay_secret,qy_appid:t.data.qy_appid,qy_wx_pay_key:t.data.qy_wx_pay_key},e.adminWxAppForm={wechat_name:t.data.wechat_name,admin_wechat_qr:t.data.admin_wechat_qr,wechat_appid:t.data.wechat_appid,wechat_secret:t.data.wechat_secret,qy_mch_id:t.data.qy_mch_id,qy_pay_secret:t.data.qy_pay_secret,admin_apiclient_cert:t.data.admin_apiclient_cert,admin_apiclient_key:t.data.admin_apiclient_key},e.agentWxAppForm={wxapplet_name:t.data.wxapplet_name,member_wechat_qr:t.data.member_wechat_qr,member_wechat_appid:t.data.member_wechat_appid,member_wechat_secret:t.data.member_wechat_secret,wx_pay_merchant_id:t.data.qy_mch_id,wx_pay_secret_key:t.data.qy_pay_secret,member_apiclient_cert:t.data.member_apiclient_cert,member_apiclient_key:t.data.member_apiclient_key},e.wxPayForm={wx_pay_merchant_id:t.data.wx_pay_merchant_id,wx_pay_secret_key:t.data.wx_pay_secret_key,apiclient_cert:t.data.apiclient_cert,apiclient_key:t.data.apiclient_key},e.bonusForm={bonus_alone:t.data.bonus_alone,bonus_proportion:t.data.bonus_proportion,bonus_today_second:t.data.bonus_today_second,bonus_month_second:t.data.bonus_month_second,bonus_small:t.data.bonus_small,bonus_explain:t.data.bonus_explain},e.capitalForm={royalty_alone:t.data.royalty_alone,royalty_proportion:t.data.royalty_proportion,royalty_today_second:t.data.royalty_today_second,royalty_month_second:t.data.royalty_month_second,royalty_small:t.data.royalty_small},e.userAgreementForm={agreementTitle:t.data.agreementTitle,agreementContent:t.data.agreementContent},e.privacyAgreementForm={privacyTitle:t.data.privacyTitle,privacyContent:t.data.privacyContent}}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},submitForm:function(e){var t=this;this.$refs[e].validate(function(a){if(a){var r=null;"baseForm"===e?((r=t.baseForm).type="basic",r.site_status=r.site_status?1:0):"qywxForm"===e?(r=t.qywxForm,o()(r,t.qywxForm),r.type="qy"):"adminWxAppForm"===e?(r=t.adminWxAppForm).type="adminWxAppForm":"agentWxAppForm"===e?(r=t.agentWxAppForm).type="agentWxAppForm":"wxPayForm"===e?(r=t.wxPayForm).type="pay":"bonusForm"===e?(r=t.bonusForm).type="bonusLimit":"capitalForm"===e?(r=t.capitalForm).type="royaltyLimit":"userAgreementForm"===e?(r=t.userAgreementForm).type="agreement":"privacyAgreementForm"===e&&((r=t.privacyAgreementForm).type="privacy"),r.id=t.id,t.$store.dispatch("GetConnect",{url:"siteconfig/update",data:r}).then(function(e){t.$message.success(e.msg),t.getSiteConfig()}).catch(function(e){t.$message.error(e.msg)})}else t.$message.error("提交失败,请检查必填项")})}}},s=(a("vnAR"),a("0Ruj"),a("KHd+")),l=Object(s.a)(n,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container  bg-gray"},[a("el-tabs",{staticClass:"box-1",attrs:{id:"site",type:"border-card"}},[a("el-tab-pane",{attrs:{label:"基础配置"}},[a("el-form",{ref:"baseForm",attrs:{model:e.baseForm,rules:e.baseFormRules,"label-width":"160px"}},[a("el-form-item",{attrs:{label:"站点名称",prop:"name"}},[a("el-input",{model:{value:e.baseForm.title,callback:function(t){e.$set(e.baseForm,"title",t)},expression:"baseForm['title']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"系统运行状态",prop:"seo_title"}},[a("el-switch",{attrs:{"active-text":"正常","inactive-text":"停站"},model:{value:e.baseForm.site_status,callback:function(t){e.$set(e.baseForm,"site_status",t)},expression:"baseForm['site_status']"}}),e._v(" "),a("el-alert",{attrs:{closable:!1,title:"停站状态下除root账号外，其他账号无法登录。",type:"info"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"页面title",prop:"seo_title"}},[a("el-input",{model:{value:e.baseForm.seo_title,callback:function(t){e.$set(e.baseForm,"seo_title",t)},expression:"baseForm['seo_title']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"扫码登录配置地址"}},[a("el-input",{model:{value:e.baseForm.qy_redirect,callback:function(t){e.$set(e.baseForm,"qy_redirect",t)},expression:"baseForm['qy_redirect']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"快捷链接"}},[a("el-radio-group",{model:{value:e.baseForm.shortcut,callback:function(t){e.$set(e.baseForm,"shortcut",t)},expression:"baseForm['shortcut']"}},[a("el-radio",{attrs:{label:1}},[e._v("开启")]),e._v(" "),a("el-radio",{attrs:{label:0}},[e._v("关闭")])],1),e._v(" "),a("el-input",{attrs:{disabled:!e.baseForm.shortcut},model:{value:e.baseForm.shortcut_name,callback:function(t){e.$set(e.baseForm,"shortcut_name",t)},expression:"baseForm['shortcut_name']"}},[a("template",{slot:"prepend"},[e._v("名称:")])],2),e._v(" "),a("el-input",{attrs:{disabled:!e.baseForm.shortcut},model:{value:e.baseForm.shortcut_url,callback:function(t){e.$set(e.baseForm,"shortcut_url",t)},expression:"baseForm['shortcut_url']"}},[a("template",{slot:"prepend"},[e._v("地址:")])],2)],1),e._v(" "),a("el-form-item",{attrs:{label:"强制用户上传头像"}},[a("el-radio-group",{model:{value:e.baseForm.avatar_status,callback:function(t){e.$set(e.baseForm,"avatar_status",t)},expression:"baseForm['avatar_status']"}},[a("el-radio",{attrs:{label:1}},[e._v("开启")]),e._v(" "),a("el-radio",{attrs:{label:0}},[e._v("关闭")])],1),e._v(" "),a("el-alert",{attrs:{closable:!1,title:"选择开启的话, 用户必须上传头像后才能正常使用系统",type:"info"}})],1),e._v(" "),a("div",{staticStyle:{"text-align":"center"}},[a("el-button",{staticClass:"bg-green",attrs:{type:"primary"},on:{click:function(t){e.submitForm("baseForm")}}},[e._v("提交")])],1)],1)],1),e._v(" "),e.queryMatch(182)?a("el-tab-pane",{attrs:{label:"小程序"}},[a("el-form",{ref:"agentWxAppForm",attrs:{model:e.agentWxAppForm,rules:e.agentWxAppFormRules,"label-width":"180px"}},[a("el-form-item",{attrs:{label:"小程序名称",prop:"name"}},[a("el-input",{model:{value:e.agentWxAppForm.wxapplet_name,callback:function(t){e.$set(e.agentWxAppForm,"wxapplet_name",t)},expression:"agentWxAppForm['wxapplet_name']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"二维码",prop:"name"}},[a("ws-upload-single-img",{attrs:{path:e.agentWxAppForm.member_wechat_qr?e.ImageUrl+e.agentWxAppForm.member_wechat_qr:""},model:{value:e.agentWxAppForm.member_wechat_qr,callback:function(t){e.$set(e.agentWxAppForm,"member_wechat_qr",t)},expression:"agentWxAppForm['member_wechat_qr']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"小程序AppId",prop:"name"}},[a("el-input",{model:{value:e.agentWxAppForm.member_wechat_appid,callback:function(t){e.$set(e.agentWxAppForm,"member_wechat_appid",t)},expression:"agentWxAppForm['member_wechat_appid']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"小程序Secret",prop:"name"}},[a("el-input",{model:{value:e.agentWxAppForm.member_wechat_secret,callback:function(t){e.$set(e.agentWxAppForm,"member_wechat_secret",t)},expression:"agentWxAppForm['member_wechat_secret']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"微信支付商户号"}},[a("el-input",{model:{value:e.agentWxAppForm.wx_pay_merchant_id,callback:function(t){e.$set(e.agentWxAppForm,"wx_pay_merchant_id",t)},expression:"agentWxAppForm['wx_pay_merchant_id']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"微信支付Api密钥"}},[a("el-input",{model:{value:e.agentWxAppForm.wx_pay_secret_key,callback:function(t){e.$set(e.agentWxAppForm,"wx_pay_secret_key",t)},expression:"agentWxAppForm['wx_pay_secret_key']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"微信支付apiclient_cert.pem"}},[a("el-input",{attrs:{rows:4,type:"textarea"},model:{value:e.agentWxAppForm.member_apiclient_cert,callback:function(t){e.$set(e.agentWxAppForm,"member_apiclient_cert",t)},expression:"agentWxAppForm['member_apiclient_cert']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"微信支付apiclient_key.pem"}},[a("el-input",{attrs:{rows:4,type:"textarea"},model:{value:e.agentWxAppForm.member_apiclient_key,callback:function(t){e.$set(e.agentWxAppForm,"member_apiclient_key",t)},expression:"agentWxAppForm['member_apiclient_key']"}})],1),e._v(" "),a("div",{staticStyle:{"text-align":"center"}},[a("el-button",{staticClass:"bg-green",attrs:{type:"primary"},on:{click:function(t){e.submitForm("agentWxAppForm")}}},[e._v("提交")])],1)],1)],1):e._e(),e._v(" "),e.queryMatch(216)?a("el-tab-pane",{attrs:{label:"企业微信"}},[a("el-form",{ref:"qywxForm",attrs:{model:e.qywxForm,rules:e.qywxFormRules,"label-width":"180px"}},[a("el-form-item",{attrs:{label:"企业微信(企业ID)"}},[a("el-input",{model:{value:e.qywxForm.company_id,callback:function(t){e.$set(e.qywxForm,"company_id",t)},expression:"qywxForm['company_id']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"通讯录secret"}},[a("el-input",{model:{value:e.qywxForm.tongxl_secret,callback:function(t){e.$set(e.qywxForm,"tongxl_secret",t)},expression:"qywxForm['tongxl_secret']\t"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"企业微信登录"}},[a("el-radio-group",{model:{value:e.qywxForm.qywxLogin,callback:function(t){e.$set(e.qywxForm,"qywxLogin",t)},expression:"qywxForm['qywxLogin']"}},[a("el-radio",{attrs:{label:1}},[e._v("开启")]),e._v(" "),a("el-radio",{attrs:{label:0}},[e._v("关闭")])],1)],1),e._v(" "),a("el-form-item",{attrs:{label:"自动推送secret"}},[a("el-input",{model:{value:e.qywxForm.push_secret,callback:function(t){e.$set(e.qywxForm,"push_secret",t)},expression:"qywxForm['push_secret']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"企业微信小程序appid"}},[a("el-input",{model:{value:e.qywxForm.qy_appid,callback:function(t){e.$set(e.qywxForm,"qy_appid",t)},expression:"qywxForm['qy_appid']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"企业微信支付密钥"}},[a("el-input",{model:{value:e.qywxForm.qy_wx_pay_key,callback:function(t){e.$set(e.qywxForm,"qy_wx_pay_key",t)},expression:"qywxForm['qy_wx_pay_key']"}})],1),e._v(" "),a("div",{staticStyle:{"text-align":"center"}},[a("el-button",{staticClass:"bg-green",attrs:{type:"primary"},on:{click:function(t){e.submitForm("qywxForm")}}},[e._v("提交")])],1)],1)],1):e._e(),e._v(" "),e.queryMatch(217)?a("el-tab-pane",{attrs:{label:"奖金设置"}},[a("el-form",{ref:"bonusForm",attrs:{model:e.bonusForm,rules:e.bonusFormRules,"label-width":"180px"}},[a("el-form-item",{attrs:{label:"单笔最高余额百分比"}},[a("el-input",{model:{value:e.bonusForm.bonus_proportion,callback:function(t){e.$set(e.bonusForm,"bonus_proportion",t)},expression:"bonusForm['bonus_proportion']"}},[a("template",{slot:"append"},[e._v("%")])],2)],1),e._v(" "),a("el-form-item",{attrs:{label:"单笔最高提现金额"}},[a("el-input",{model:{value:e.bonusForm.bonus_alone,callback:function(t){e.$set(e.bonusForm,"bonus_alone",t)},expression:"bonusForm['bonus_alone']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"最少提现"}},[a("el-input",{model:{value:e.bonusForm.bonus_small,callback:function(t){e.$set(e.bonusForm,"bonus_small",t)},expression:"bonusForm['bonus_small']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"每日提现次数"}},[a("el-input",{model:{value:e.bonusForm.bonus_today_second,callback:function(t){e.$set(e.bonusForm,"bonus_today_second",t)},expression:"bonusForm['bonus_today_second']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"每月提现次数"}},[a("el-input",{model:{value:e.bonusForm.bonus_month_second,callback:function(t){e.$set(e.bonusForm,"bonus_month_second",t)},expression:"bonusForm['bonus_month_second']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"规则说明"}},[a("el-input",{attrs:{rows:3,type:"textarea"},model:{value:e.bonusForm.bonus_explain,callback:function(t){e.$set(e.bonusForm,"bonus_explain",t)},expression:"bonusForm['bonus_explain']"}})],1),e._v(" "),a("div",{staticStyle:{"text-align":"center"}},[a("el-button",{staticClass:"bg-green",attrs:{type:"primary"},on:{click:function(t){e.submitForm("bonusForm")}}},[e._v("提交")])],1)],1)],1):e._e(),e._v(" "),a("el-tab-pane",{attrs:{label:"用户协议"}},[a("el-form",{ref:"userAgreementForm",attrs:{model:e.userAgreementForm,"label-width":"180px"}},[a("el-form-item",{attrs:{label:"用户协议名称",prop:"name"}},[a("el-input",{model:{value:e.userAgreementForm.agreementTitle,callback:function(t){e.$set(e.userAgreementForm,"agreementTitle",t)},expression:"userAgreementForm['agreementTitle']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"编辑",prop:"content"}},[a("ws-editor",{model:{value:e.userAgreementForm.agreementContent,callback:function(t){e.$set(e.userAgreementForm,"agreementContent",t)},expression:"userAgreementForm['agreementContent']"}})],1),e._v(" "),a("div",{staticStyle:{"text-align":"center"}},[a("el-button",{staticClass:"bg-green",attrs:{type:"primary"},on:{click:function(t){e.submitForm("userAgreementForm")}}},[e._v("提交")])],1)],1)],1),e._v(" "),a("el-tab-pane",{attrs:{label:"隐私协议"}},[a("el-form",{ref:"privacyAgreementForm",attrs:{model:e.privacyAgreementForm,"label-width":"180px"}},[a("el-form-item",{attrs:{label:"隐私协议名称",prop:"name"}},[a("el-input",{model:{value:e.privacyAgreementForm.privacyTitle,callback:function(t){e.$set(e.privacyAgreementForm,"privacyTitle",t)},expression:"privacyAgreementForm['privacyTitle']"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"编辑",prop:"content"}},[a("ws-editor",{model:{value:e.privacyAgreementForm.privacyContent,callback:function(t){e.$set(e.privacyAgreementForm,"privacyContent",t)},expression:"privacyAgreementForm['privacyContent']"}})],1),e._v(" "),a("div",{staticStyle:{"text-align":"center"}},[a("el-button",{staticClass:"bg-green",attrs:{type:"primary"},on:{click:function(t){e.submitForm("privacyAgreementForm")}}},[e._v("提交")])],1)],1)],1)],1)],1)},[],!1,null,"d9b0cc98",null);l.options.__file="site.vue";t.default=l.exports},vnAR:function(e,t,a){"use strict";var r=a("HuyM");a.n(r).a}}]);