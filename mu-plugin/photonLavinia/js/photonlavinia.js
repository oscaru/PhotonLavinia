
window.serviceUrl = 'http://rtvcm.local/pictures';

//sonar>
(function(e,h,l,c){e.fn.sonar=function(o,n){if(typeof o==="boolean"){n=o;o=c}return e.sonar(this[0],o,n)};var f=l.body,a="scrollin",m="scrollout",b=function(r,n,t){if(r){f||(f=l.body);var s=r,u=0,v=f.offsetHeight,o=h.innerHeight||l.documentElement.clientHeight||f.clientHeight||0,q=l.documentElement.scrollTop||h.pageYOffset||f.scrollTop||0,p=r.offsetHeight||0;if(!r.sonarElemTop||r.sonarBodyHeight!==v){if(s.offsetParent){do{u+=s.offsetTop}while(s=s.offsetParent)}r.sonarElemTop=u;r.sonarBodyHeight=v}n=n===c?0:n;return(!(r.sonarElemTop+(t?0:p)<q-n)&&!(r.sonarElemTop+(t?p:0)>q+o+n))}},d={},j=0,i=function(){setTimeout(function(){var s,o,t,q,p,r,n;for(t in d){o=d[t];for(r=0,n=o.length;r<n;r++){q=o[r];s=q.elem;p=b(s,q.px,q.full);if(t===m?!p:p){if(!q.tr){if(s[t]){e(s).trigger(t);q.tr=1}else{o.splice(r,1);r--;n--}}}else{q.tr=0}}}},25)},k=function(n,o){n[o]=0},g=function(r,p){var t=p.px,q=p.full,s=p.evt,o=b(r,t,q),n=0;r[s]=1;if(s===m?!o:o){setTimeout(function(){e(r).trigger(s===m?m:a)},0);n=1}d[s].push({elem:r,px:t,full:q,tr:n});if(!j){e(h).bind("scroll",i);j=1}};e.sonar=b;d[a]=[];e.event.special[a]={add:function(n){var p=n.data||{},o=this;if(!o[a]){g(this,{px:p.distance,full:p.full,evt:a})}},remove:function(n){k(this,a)}};d[m]=[];e.event.special[m]={add:function(n){var p=n.data||{},o=this;if(!o[m]){g(o,{px:p.distance,full:p.full,evt:m})}},remove:function(n){k(this,m)}}})(jQuery,window,document);

$(document).ready(function(){

(function($) {
	photonLav_lazy_load_init();
	$( 'body' ).bind( 'post-load', photonLav_lazy_load_init ); // Work with WP.com infinite scroll

        var serviceUrl;
        var lazyActive = true;
	function photonLav_lazy_load_init() {
                _log('lazy init')
                
		$( 'img[data-lazy-src]' ).bind( 'scrollin', { distance: 200 }, function() {
			photonLav_lazy_load_image( this );
		});

		// We need to force load gallery images in Jetpack Carousel and give up lazy-loading otherwise images don't show up correctly
		$( '[data-carousel-extra]' ).each( function() {
			$( this ).find( 'img[data-lazy-src]' ).each( function() {
				photonLav_lazy_load_image( this );
			} );		
		} );
	}

        function photonLav_getElementSize(elem){
            
            var size =  { width : getWitdh($(elem)),
                        height :  getHeight($(elem))
                    };
            return size;
        }
        
        function getWitdh($elem){
            var $width = $elem.width();
            if($width <= 1) return getWitdh($elem.parent());
            return $width;
            
        }
        
        function getHeight($elem){
            var $height = $elem.height();
            if($height <= 1) return getHeight($elem.parent());
            return $height;
            
        }
        
        function photonLav_createRequestUri(url,sizeCalculate){
            var size = getAproximateSize(sizeCalculate.width);
            serviceUrl = serviceUrl || window.serviceUrl;
           
            query = url.indexOf('?');
            if(query != -1){
                url = url.substring(0,query);
                query = url.substring(query);
  
            }else {
                query = '';
            }


            var partes = parse_url_regex(url);
            
            var newUrl = serviceUrl+'/'+partes.protocol+'/'+
                         partes.domain+'/'+partes.path+'/'+'_lsize_'+size[0]+'x'+size[1]+
                         '/'+partes.file+query;
           // var uri = encodeURIComponent(origImg);
            return newUrl;
        }
        
	function photonLav_lazy_load_image( img ) {
		var $img = jQuery( img ),
			src = $img.attr( 'data-lazy-src' );

		if ( ! src || 'undefined' === typeof( src ) )
			return;

		$img.unbind( 'scrollin' ) // remove event binding
			.hide()
			.removeAttr( 'data-lazy-src' )
			.attr( 'data-lazy-loaded', 'true' );

                _log(['lazyActive',photonLav_getElementSize(img)]);
		img.src = (lazyActive)? photonLav_createRequestUri(src,photonLav_getElementSize(img)) : src;
		$img.fadeIn();
	}
        
        function getAproximateSize(width){
            var allowed = [
                [320, 320],
                [640 , 480],
                [960, 640],
                [1024, 768],
                [1920, 1200]
            ];
            
            for (var i in allowed ) {
                if(width <= allowed[i][0]){
                    return allowed[i];
                }
            }
            return allowed[i];
        }
        
        function parse_url_regex(url) {
            var parse_url = /^(?:([A-Za-z]+):)?(\/{0,3})([0-9.\-A-Za-z]+)(?::(\d+))?(?:\/([^?#]*))?(?:\?([^#]*))?(?:#(.*))?$/;
            var result = parse_url.exec(url);
            var protocol = result[1] || 'http';
            var path = result[5].split("/");
            var file = path.pop();
            var path = path.join('/');
            return {protocol: protocol, domain:result[3], file:file , path:path};
        }
        function _log(tolog){
            console.log(tolog);
        }
})(jQuery);
});