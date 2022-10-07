!function(){"use strict";var e,t={128:function(e,t,o){var r=window.wp.blocks,n=window.wp.element,l=window.lodash,s=window.wp.blockEditor,i=window.wp.components,a=window.wp.i18n,c=window.React,h=o.n(c),p=function(e,t){return p=Object.setPrototypeOf||{__proto__:[]}instanceof Array&&function(e,t){e.__proto__=t}||function(e,t){for(var o in t)t.hasOwnProperty(o)&&(e[o]=t[o])},p(e,t)},u=function(){return u=Object.assign||function(e){for(var t,o=1,r=arguments.length;o<r;o++)for(var n in t=arguments[o])Object.prototype.hasOwnProperty.call(t,n)&&(e[n]=t[n]);return e},u.apply(this,arguments)},d="Pixel",m="Percent",f={unit:m,value:.8};function v(e){return"number"==typeof e?{unit:m,value:100*e}:"string"==typeof e?e.match(/^(\d*(\.\d+)?)px$/)?{unit:d,value:parseFloat(e)}:e.match(/^(\d*(\.\d+)?)%$/)?{unit:m,value:parseFloat(e)}:(console.warn('scrollThreshold format is invalid. Valid formats: "120px", "50%"...'),f):(console.warn("scrollThreshold should be string or number"),f)}var g=function(e){function t(t){var o=e.call(this,t)||this;return o.lastScrollTop=0,o.actionTriggered=!1,o.startY=0,o.currentY=0,o.dragging=!1,o.maxPullDownDistance=0,o.getScrollableTarget=function(){return o.props.scrollableTarget instanceof HTMLElement?o.props.scrollableTarget:"string"==typeof o.props.scrollableTarget?document.getElementById(o.props.scrollableTarget):(null===o.props.scrollableTarget&&console.warn("You are trying to pass scrollableTarget but it is null. This might\n        happen because the element may not have been added to DOM yet.\n        See https://github.com/ankeetmaini/react-infinite-scroll-component/issues/59 for more info.\n      "),null)},o.onStart=function(e){o.lastScrollTop||(o.dragging=!0,e instanceof MouseEvent?o.startY=e.pageY:e instanceof TouchEvent&&(o.startY=e.touches[0].pageY),o.currentY=o.startY,o._infScroll&&(o._infScroll.style.willChange="transform",o._infScroll.style.transition="transform 0.2s cubic-bezier(0,0,0.31,1)"))},o.onMove=function(e){o.dragging&&(e instanceof MouseEvent?o.currentY=e.pageY:e instanceof TouchEvent&&(o.currentY=e.touches[0].pageY),o.currentY<o.startY||(o.currentY-o.startY>=Number(o.props.pullDownToRefreshThreshold)&&o.setState({pullToRefreshThresholdBreached:!0}),o.currentY-o.startY>1.5*o.maxPullDownDistance||o._infScroll&&(o._infScroll.style.overflow="visible",o._infScroll.style.transform="translate3d(0px, "+(o.currentY-o.startY)+"px, 0px)")))},o.onEnd=function(){o.startY=0,o.currentY=0,o.dragging=!1,o.state.pullToRefreshThresholdBreached&&(o.props.refreshFunction&&o.props.refreshFunction(),o.setState({pullToRefreshThresholdBreached:!1})),requestAnimationFrame((function(){o._infScroll&&(o._infScroll.style.overflow="auto",o._infScroll.style.transform="none",o._infScroll.style.willChange="unset")}))},o.onScrollListener=function(e){"function"==typeof o.props.onScroll&&setTimeout((function(){return o.props.onScroll&&o.props.onScroll(e)}),0);var t=o.props.height||o._scrollableNode?e.target:document.documentElement.scrollTop?document.documentElement:document.body;o.actionTriggered||((o.props.inverse?o.isElementAtTop(t,o.props.scrollThreshold):o.isElementAtBottom(t,o.props.scrollThreshold))&&o.props.hasMore&&(o.actionTriggered=!0,o.setState({showLoader:!0}),o.props.next&&o.props.next()),o.lastScrollTop=t.scrollTop)},o.state={showLoader:!1,pullToRefreshThresholdBreached:!1,prevDataLength:t.dataLength},o.throttledOnScrollListener=function(e,t,o,r){var n,l=!1,s=0;function i(){n&&clearTimeout(n)}function a(){var a=this,c=Date.now()-s,h=arguments;function p(){s=Date.now(),o.apply(a,h)}function u(){n=void 0}l||(r&&!n&&p(),i(),void 0===r&&c>e?p():!0!==t&&(n=setTimeout(r?u:p,void 0===r?e-c:e)))}return"boolean"!=typeof t&&(r=o,o=t,t=void 0),a.cancel=function(){i(),l=!0},a}(150,o.onScrollListener).bind(o),o.onStart=o.onStart.bind(o),o.onMove=o.onMove.bind(o),o.onEnd=o.onEnd.bind(o),o}return function(e,t){function __(){this.constructor=e}p(e,t),e.prototype=null===t?Object.create(t):(__.prototype=t.prototype,new __)}(t,e),t.prototype.componentDidMount=function(){if(void 0===this.props.dataLength)throw new Error('mandatory prop "dataLength" is missing. The prop is needed when loading more content. Check README.md for usage');if(this._scrollableNode=this.getScrollableTarget(),this.el=this.props.height?this._infScroll:this._scrollableNode||window,this.el&&this.el.addEventListener("scroll",this.throttledOnScrollListener),"number"==typeof this.props.initialScrollY&&this.el&&this.el instanceof HTMLElement&&this.el.scrollHeight>this.props.initialScrollY&&this.el.scrollTo(0,this.props.initialScrollY),this.props.pullDownToRefresh&&this.el&&(this.el.addEventListener("touchstart",this.onStart),this.el.addEventListener("touchmove",this.onMove),this.el.addEventListener("touchend",this.onEnd),this.el.addEventListener("mousedown",this.onStart),this.el.addEventListener("mousemove",this.onMove),this.el.addEventListener("mouseup",this.onEnd),this.maxPullDownDistance=this._pullDown&&this._pullDown.firstChild&&this._pullDown.firstChild.getBoundingClientRect().height||0,this.forceUpdate(),"function"!=typeof this.props.refreshFunction))throw new Error('Mandatory prop "refreshFunction" missing.\n          Pull Down To Refresh functionality will not work\n          as expected. Check README.md for usage\'')},t.prototype.componentWillUnmount=function(){this.el&&(this.el.removeEventListener("scroll",this.throttledOnScrollListener),this.props.pullDownToRefresh&&(this.el.removeEventListener("touchstart",this.onStart),this.el.removeEventListener("touchmove",this.onMove),this.el.removeEventListener("touchend",this.onEnd),this.el.removeEventListener("mousedown",this.onStart),this.el.removeEventListener("mousemove",this.onMove),this.el.removeEventListener("mouseup",this.onEnd)))},t.prototype.componentDidUpdate=function(e){this.props.dataLength!==e.dataLength&&(this.actionTriggered=!1,this.setState({showLoader:!1}))},t.getDerivedStateFromProps=function(e,t){return e.dataLength!==t.prevDataLength?u(u({},t),{prevDataLength:e.dataLength}):null},t.prototype.isElementAtTop=function(e,t){void 0===t&&(t=.8);var o=e===document.body||e===document.documentElement?window.screen.availHeight:e.clientHeight,r=v(t);return r.unit===d?e.scrollTop<=r.value+o-e.scrollHeight+1:e.scrollTop<=r.value/100+o-e.scrollHeight+1},t.prototype.isElementAtBottom=function(e,t){void 0===t&&(t=.8);var o=e===document.body||e===document.documentElement?window.screen.availHeight:e.clientHeight,r=v(t);return r.unit===d?e.scrollTop+o>=e.scrollHeight-r.value:e.scrollTop+o>=r.value/100*e.scrollHeight},t.prototype.render=function(){var e=this,t=u({height:this.props.height||"auto",overflow:"auto",WebkitOverflowScrolling:"touch"},this.props.style),o=this.props.hasChildren||!!(this.props.children&&this.props.children instanceof Array&&this.props.children.length),r=this.props.pullDownToRefresh&&this.props.height?{overflow:"auto"}:{};return h().createElement("div",{style:r,className:"infinite-scroll-component__outerdiv"},h().createElement("div",{className:"infinite-scroll-component "+(this.props.className||""),ref:function(t){return e._infScroll=t},style:t},this.props.pullDownToRefresh&&h().createElement("div",{style:{position:"relative"},ref:function(t){return e._pullDown=t}},h().createElement("div",{style:{position:"absolute",left:0,right:0,top:-1*this.maxPullDownDistance}},this.state.pullToRefreshThresholdBreached?this.props.releaseToRefreshContent:this.props.pullDownToRefreshContent)),this.props.children,!this.state.showLoader&&!o&&this.props.hasMore&&this.props.loader,this.state.showLoader&&this.props.hasMore&&this.props.loader,!this.props.hasMore&&this.props.endMessage))},t}(c.Component),b=g,w=window.wp.apiFetch,_=o.n(w),E=window.wp.url;function y(e){let{onItemSelect:t}=e;const{sites:o=[],post_types:r=[]}=multisiteSharedBlocksEditorData,l=Object.keys(o).map((e=>({label:o[e],value:e}))),s=Object.keys(r).map((e=>({label:r[e],value:e}))),[c,h]=(0,n.useState)({search:"",postType:"",site:0}),[p,u]=(0,n.useState)([]),[d,m]=(0,n.useState)(!0),[f,v]=(0,n.useState)(1),[g,w]=(0,n.useState)(!1),y=()=>{u([]),v(1),w(!1),m(!0)};(0,n.useEffect)((()=>{const e=void 0===window.AbortController?void 0:new window.AbortController;return k(c,e),()=>null==e?void 0:e.abort()}),[c]);const k=(e,t)=>{(function(e,t){const o=(0,E.addQueryArgs)("/multisite-shared-blocks/v1/search",{search:e.search||"",post_type:e.postType&&""!==e.postType?[e.postType]:[],site_in:e.site&&0!==e.site?[e.site]:[],page:e.page||1,per_page:e.per_page||30});return _()({path:o,signal:null==t?void 0:t.signal})})({...e,page:f},t).then((e=>p.length||e.length?e.length?(u([...p,...e]),v(f+1),m(!0),void w(!0)):void w(!1):(m(!1),void w(!1)))).catch((e=>{w(!1),"rest_search_invalid_page_number"!==e.code&&(u([]),m(!1),v(1))}))};return(0,n.createElement)("div",{className:"shared-block-selector"},(0,n.createElement)("div",{className:"shared-block-selector__filters"},(0,n.createElement)("div",{className:"filter filter--sites"},(0,n.createElement)(i.SelectControl,{label:(0,a.__)("Filter by site","multisite-shared-blocks"),value:c.site,options:[{value:0,label:(0,a.__)("All","multisite-shared-blocks")},...l],onChange:e=>{var t;t="0"!==e?+e:0,h({...c,site:t}),y()}})),(0,n.createElement)("div",{className:"filter filter--posttypes"},(0,n.createElement)(i.SelectControl,{label:(0,a.__)("Filter by post type","multisite-shared-blocks"),value:c.postType,options:[{value:0,label:(0,a.__)("All","multisite-shared-blocks")},...s],onChange:e=>{var t;t="0"!==e?e:"",h({...c,postType:t}),y()}})),(0,n.createElement)(i.TextControl,{label:(0,a.__)("Search for a shared block","multisite-shared-blocks"),className:"filter filter--search",onChange:e=>{var t;t=e,h({...c,search:t}),y()}})),(0,n.createElement)("div",{className:"shared-block-selector__results"},d?(0,n.createElement)(b,{dataLength:p.length,next:()=>k(c),hasMore:g,height:300,loader:(0,n.createElement)(i.Spinner,null)},p.map((e=>(0,n.createElement)("div",{key:e.id,className:"results__item"},(0,n.createElement)(i.Button,{variant:"link",label:(0,a.sprintf)(//translators: %s shared block title
(0,a.__)('Select block "%s"',"multisite-shared-blocks"),e.block_title),onClick:()=>{t(e)}},`${e.full_block_title}`))))):(0,n.createElement)("div",{className:"no-results"},(0,a.__)("No results.","multisite-shared-blocks"))))}var k=window.wp.primitives,T=(0,n.createElement)(k.SVG,{xmlns:"http://www.w3.org/2000/svg",width:"24",height:"24",fill:"none",viewBox:"0 0 24 24"},(0,n.createElement)(k.Path,{fill:"#000","fill-rule":"evenodd",d:"M14.247 0H9.753c-.715 0-1.327.647-1.327 1.402V4.8c0 .755.612 1.402 1.327 1.402h1.736v2.034c-1.599.242-2.857 1.601-3.04 3.306h-1.3v-1.295c0-.755-.613-1.402-1.328-1.402H1.328C.613 8.845 0 9.492 0 10.247v3.398c0 .755.613 1.402 1.328 1.402H5.82c.715 0 1.328-.647 1.328-1.402V12.62h1.329c.262 1.602 1.48 2.859 3.011 3.09v2.088H9.753c-.715 0-1.327.647-1.327 1.402v3.398c0 .755.612 1.402 1.327 1.402h4.494c.715 0 1.328-.647 1.328-1.402V19.2c0-.809-.613-1.402-1.328-1.402H12.51V15.71c1.53-.231 2.75-1.488 3.011-3.09h1.329v1.025c0 .755.613 1.402 1.328 1.402h4.493c.715 0 1.328-.647 1.328-1.402v-3.398c0-.755-.613-1.402-1.328-1.402H18.18c-.715 0-1.328.647-1.328 1.402v1.295h-1.3c-.183-1.705-1.441-3.064-3.04-3.306V6.202h1.736c.715 0 1.328-.647 1.328-1.402V1.402C15.575.593 14.962 0 14.247 0Zm.306 4.746c0 .162-.153.324-.306.324H9.753c-.153 0-.306-.162-.306-.324V1.402c0-.162.153-.323.306-.323h4.494c.153 0 .306.108.306.323v3.344ZM12 9.276c-1.41 0-2.553 1.208-2.553 2.697 0 1.49 1.143 2.697 2.553 2.697s2.553-1.208 2.553-2.697c0-1.49-1.143-2.697-2.553-2.697Zm10.979 4.369c0 .162-.153.324-.307.324H18.18c-.153 0-.307-.162-.307-.324v-3.398c0-.162.154-.323.307-.323h4.493c.154 0 .307.161.307.323v3.398Zm-17.158.324c.153 0 .307-.162.307-.324v-3.398c0-.162-.154-.323-.307-.323H1.328c-.154 0-.307.161-.307.323v3.398c0 .162.153.324.307.324H5.82Zm8.426 8.898c.153 0 .306-.161.306-.323v-3.398c0-.162-.153-.323-.306-.323H9.753c-.153 0-.306.161-.306.323v3.398c0 .162.153.323.306.323h4.494Z","clip-rule":"evenodd"}));(0,r.registerBlockType)("multisite-shared-blocks/shared-block",{icon:T,edit:function(e){let{attributes:t,setAttributes:o}=e;const{blockId:r,blockTitle:c,display:h}=t,[p,u]=(0,n.useState)(!1),d=e=>{o({display:e})};return(0,n.createElement)("div",(0,s.useBlockProps)(),(0,n.createElement)(s.InspectorControls,null,(0,n.createElement)(i.PanelBody,{title:(0,a.__)("Display options","multisite-shared-blocks")},(0,n.createElement)(i.ButtonGroup,null,(0,n.createElement)(i.Button,{onClick:()=>d("full"),isPressed:"full"===h},(0,a.__)("Full content","multisite-shared-blocks")),(0,n.createElement)(i.Button,{onClick:()=>d("excerpt"),isPressed:"excerpt"===h},(0,a.__)("Excerpt","multisite-shared-blocks"))))),(0,l.isEmpty)(r)||p?(0,n.createElement)("div",{className:"shared-block-selector-wrapper"},!1===(0,l.isEmpty)(r)&&(0,n.createElement)("div",{className:"shared-block-selector-wrapper__cancel"},(0,n.createElement)("div",{className:"shared-block-selector-wrapper__cancel--message"},(0,a.__)("Cancel changes and keep current selected block ?","multisite-shared-blocks")),(0,n.createElement)(i.Button,{className:"shared-block-selector-wrapper__cancel--button",variant:"secondary",isDestructive:!0,onClick:()=>u(!1)},(0,a.__)("Cancel changes","multisite-shared-blocks"))),(0,n.createElement)(y,{onItemSelect:e=>{var t;o({siteId:(t=e).site_id,postId:t.post_id,blockId:t.block_id,blockTitle:t.full_block_title}),u(!1)}})):(0,n.createElement)(i.Placeholder,{icon:T,label:(0,a.__)("Shared Block","multisite-shared-blocks"),instructions:(m=h,"full"===m?(0,n.createElement)("p",null,(0,n.createInterpolateElement)((0,a.sprintf)(// translators: %s is the shared block's title
(0,a.__)('Shared block <strong>"%s"</strong> will be displayed here.',"multisite-shared-blocks"),c),{strong:(0,n.createElement)("strong",null)})):(0,n.createElement)("p",null,(0,n.createInterpolateElement)((0,a.sprintf)(// translators: %s is the shared block's title
(0,a.__)('Excerpt for the shared block <strong>"%s"</strong> will be displayed here.',"multisite-shared-blocks"),c),{strong:(0,n.createElement)("strong",null)}))),className:"shared-block-placeholder"},(0,n.createElement)(i.Button,{variant:"primary",onClick:()=>u(!0)},(0,a.__)("Choose a new block","multisite-shared-blocks"))));var m}})}},o={};function r(e){var n=o[e];if(void 0!==n)return n.exports;var l=o[e]={exports:{}};return t[e](l,l.exports,r),l.exports}r.m=t,e=[],r.O=function(t,o,n,l){if(!o){var s=1/0;for(h=0;h<e.length;h++){o=e[h][0],n=e[h][1],l=e[h][2];for(var i=!0,a=0;a<o.length;a++)(!1&l||s>=l)&&Object.keys(r.O).every((function(e){return r.O[e](o[a])}))?o.splice(a--,1):(i=!1,l<s&&(s=l));if(i){e.splice(h--,1);var c=n();void 0!==c&&(t=c)}}return t}l=l||0;for(var h=e.length;h>0&&e[h-1][2]>l;h--)e[h]=e[h-1];e[h]=[o,n,l]},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,{a:t}),t},r.d=function(e,t){for(var o in t)r.o(t,o)&&!r.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){var e={261:0,570:0};r.O.j=function(t){return 0===e[t]};var t=function(t,o){var n,l,s=o[0],i=o[1],a=o[2],c=0;if(s.some((function(t){return 0!==e[t]}))){for(n in i)r.o(i,n)&&(r.m[n]=i[n]);if(a)var h=a(r)}for(t&&t(o);c<s.length;c++)l=s[c],r.o(e,l)&&e[l]&&e[l][0](),e[l]=0;return r.O(h)},o=self.webpackChunkmultisite_shared_blocks=self.webpackChunkmultisite_shared_blocks||[];o.forEach(t.bind(null,0)),o.push=t.bind(null,o.push.bind(o))}();var n=r.O(void 0,[570],(function(){return r(128)}));n=r.O(n)}();