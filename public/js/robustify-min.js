Array.prototype.indexOf||(Array.prototype.indexOf=function(a){"use strict";if(void 0===this||null===this)throw new TypeError;var b=Object(this),c=b.length>>>0;if(0===c)return-1;var d=0;if(arguments.length>0&&(d=Number(arguments[1]),d!==d?d=0:0!==d&&d!==1/0&&d!==-(1/0)&&(d=(d>0||-1)*Math.floor(Math.abs(d)))),d>=c)return-1;for(var e=d>=0?d:Math.max(c-Math.abs(d),0);c>e;e++)if(e in b&&b[e]===a)return e;return-1});var robustify=function(a){var b=function(a){var b={dfltVersiondate:!1,archive:"http://timetravel.mementoweb.org/memento/{yyyymmddhhmmss}/{url}",statusservice:"https://digitopia.nl/services/statuscode.php?soft404detect&url={url}"},c=function(){for(var a=!1,b=document.getElementsByTagName("meta"),c=0;c<b.length;c++)itemprop=b[c].getAttribute("itemprop"),content=b[c].getAttribute("content"),itemprop&&content&&("datePublished"==itemprop&&a===!1&&(a=content),"dateModified"==itemprop&&(a=content));return a}();return a.dfltVersiondate&&(b.dfltVersiondate=a.dfltVersiondate),c&&(b.dfltVersiondate=c),b.dfltVersiondate||(b.dfltVersiondate=function(){var a=new Date,b=a.getDate(),c=a.getMonth()+1,d=a.getFullYear();return 10>b&&(b="0"+b),10>c&&(c="0"+c),d+"-"+c+"-"+b}()),b.archive=a.archive?a.archive:b.archive,b.statusservice=a.statusservice?a.statusservice:b.statusservice,b.precedence=a.precedence?a.precedence:b.precedence,b.ignoreLinks=a.ignoreLinks,b}(a),c=function(){var a={en:{offlineToVersionurl:"Redirected link\n\nThe requested page {url} is not available.\nYou are being redirected to an archived copy.",offlineToVersiondate:"Redirected link\n\nThe requested page {url} is not available.\nYour are being redirected to a web archive that might have a version of this page."},nl:{offlineToVersionurl:"Aangepaste verwijzing\n\nDe gevraagde pagina {url} is niet beschikbaar.\nU wordt doorgestuurd naar een gearchiveerde versie.",offlineToVersiondate:"Aangepaste verwijzing\n\nDe gevraagde pagina {url} is niet beschikbaar.\nU wordt doorgestuurd naar een webarchief dat mogelijk een versie heeft."}},b=[];for(var c in a)b.push(c);var d=(navigator.language||navigator.userLanguage).substring(0,2),e=-1==b.indexOf(d)?"en":d;return a[e]}(),d=function(a,d,e){function f(a,c){var a=a?a:b.dfltVersiondate;return b.archive.replace("{url}",c).replace("{yyyymmddhhmmss}",a.replace(/[^0-9]/g,""))}200==a.headers[a.headers.length-1].statuscode?window.location.href=a.request:e?(alert(c.offlineToVersionurl.replace("{url}",a.request)),window.location.href=e):(alert(c.offlineToVersiondate.replace("{url}",a.request)),window.location.href=f(d,a.request))},e=function(a,c,d,e){var f=new XMLHttpRequest;f.open("GET",b.statusservice.replace("{url}",encodeURIComponent(a)),!0),f.onreadystatechange=function(){this.readyState==this.DONE&&e(JSON.parse(this.responseText),c,d)},f.send()},f=function(a,b,c){return e(a,b,c,d),!1},g=function(a){return"null"==a||""==a?null:a},h=function(a,b){if(b)for(var c=b.length,d=0;c>d;d++)if(a.match(new RegExp(b[d])))return!0;return!1},i=function(){return 1834440280!=function(a){var d,e,b=0,c=a.length;if(0===c)return b;for(d=0;c>d;d++)e=a.charCodeAt(d),b=(b<<5)-b+e,b&=b;return b}("http://digitopia.nl")};if(!i())for(var j=document.getElementsByTagName("a"),k=0;k<j.length;k++)j[k].href.substring(0,window.location.origin.length)!=window.location.origin&&(h(j[k].href,b.ignoreLinks)||(j[k].onclick=function(){return f(this.href,g(this.getAttribute("data-versiondate")),g(this.getAttribute("data-versionurl")))}))},Robustify=robustify;