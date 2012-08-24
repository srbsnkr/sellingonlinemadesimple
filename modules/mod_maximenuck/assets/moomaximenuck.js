/**
 * @copyright	Copyright (C) 2011 Cedric KEIFLIN alias ced1870
 * http://www.joomlack.fr
 * Module Maximenu CK
 * @license		GNU/GPL
 * */

if (typeof(MooTools) != 'undefined'){

    var DropdownMaxiMenu = new Class({
        Implements: Options,
        options: {    //options par defaut si aucune option utilisateur n'est renseignee
			
            mooTransition : 'Bounce',
            mooEase : 'easeOut',
            mooDuree : 500,
            mooDureeout : 500,
            useOpacity : '0',
            menuID : 'maximenuck',
            testoverflow : '1',
            orientation : '0',
            style : 'moomenu',
            opentype : 'open',
            direction : 'direction',
            directionoffset1 : '30',
            directionoffset2 : '30',
            dureeIn : 0,
            dureeOut : 500,
            ismobile : false,
            showactivesubitems : '0'
        },
			
        initialize: function(element,options) {
            if (!element) return false;
			
            this.setOptions(options); //enregistre les options utilisateur

            var maduree = this.options.mooDuree;
            var madureeout = this.options.mooDureeout;
            var matransition = this.options.mooTransition;
            var monease = this.options.mooEase;
            var useopacity = this.options.useOpacity;
            var dureeout = this.options.dureeOut;
            var dureein = this.options.dureeIn;
            var menuID = this.options.menuID;
            var testoverflow = this.options.testoverflow;
            var orientation = this.options.orientation;
            var opentype = this.options.opentype;
            var style = this.options.style;
            var direction = this.options.direction;
            var directionoffset1 = this.options.directionoffset1;
            var directionoffset2 = this.options.directionoffset2;
            var showactivesubitems = this.options.showactivesubitems;
            var ismobile = this.options.ismobile;

            var els = element.getElements('li.maximenuck.parent');

            els.each(function(el) {
										
                if (el.getElement('div.floatck') != null) {
                    el.conteneur = el.getElement('div.floatck');
                    el.slideconteneur = el.getElement('div.maxidrop-main');
						
                    el.conteneurul = el.getElements('div.floatck ul');
                    el.conteneurul.setStyle('position','static');

                    
                    if (direction =='inverse') {
                        if (orientation =='0') {
                            if (el.hasClass('level1')) {
                                el.conteneur.setStyle('bottom',directionoffset1+'px');
                            } else {
                                el.conteneur.setStyle('bottom',directionoffset2+'px');
                            }
                        } else {
                            if (el.hasClass('level1')) {
                                el.conteneur.setStyle('right',directionoffset1+'px');
                            } else {
                                el.conteneur.setStyle('right',directionoffset2+'px');
                            }
                        }
                    }
						
                    el.conteneur.mh = el.conteneur.clientHeight;
                    el.conteneur.mw = el.conteneur.clientWidth;
                    el.duree = maduree;
                    el.madureeout = madureeout;
                    el.transition = matransition;
                    el.ease = monease;
                    el.useopacity = useopacity;
                    el.orientation = orientation;
                    el.opentype = opentype;
                    el.direction = direction;
                    el.showactivesubitems = showactivesubitems;
                    el.zindex = el.getStyle('z-index');
                    el.createFxMaxiCK();

                    if (style == 'clickclose') {
                        el.addEvent('mouseenter',function() {

                            if (testoverflow == '1') this.testOverflowMaxiCK(menuID);
                            if (el.hasClass('level1') && el.hasClass('parent') && el.status != 'show') {
                                els.each(function(el2){
                                    if (el2.status == 'show') {
                                        //el2.getElement('div.floatck').setStyle('height','0');
                                        element.getElements('div.floatck').setStyle('left','-999em');
                                        el2.status = 'hide';
                                        el2.setStyle('z-index',12001);
                                    }
                                });

                            }
                            el.setStyle('z-index',15000);
                            this.showMaxiCK();

                        });

                        el.getElement('.maxiclose').addEvent('click',function() {
                            el.setStyle('z-index',12001);
                            el.hideMaxiCK();
                        });

                    } else if (style == 'click') {

                        var levels = ["level1", "level1", "level2", "level3", "level4"];

                        if (el.hasClass('parent') && el.getFirst('a.maximenuck')) {
                            el.redirection = el.getFirst('a.maximenuck').getProperty('href');
                            el.getFirst('a.maximenuck').setProperty('href','javascript:void(0)');
                            el.hasBeenClicked = false;
                        }

                        // hide when clicked outside
                        if (ismobile) {
                            $(document.body).addEvent('click',function(e) {
                                if(element && !e.target || !$(e.target).getParents().contains(element)) {
                                    el.hasBeenClicked = false;
                                    el.hideMaxiCK();
                                }
                            });
                        }
                        

                        el.getElement('span.titreck').addEvent('click',function() {
                            // set the redirection again for mobile
                            if (el.hasBeenClicked == true && ismobile) {
                                el.getFirst('a.maximenuck').setProperty('href',el.redirection);
                            }

                            el.hasBeenClicked = true;
                            if (testoverflow == '1') this.testOverflowMaxiCK(menuID);
                            if (el.status == 'show') {
                                // el.setStyle('z-index',12001);
                                el.hideMaxiCK();
                            } else {
                                levels.each(function(level){

                                    if (el.hasClass(level) && el.hasClass('parent') && el.status != 'show') {

                                        els.each(function(el2){
                                            if (el2.status == 'show' && el2.hasClass(level)) {
                                                //el2.getElement('div.floatck').setStyle('height','0');
                                                element.getElements('li.'+level+' div.floatck').setStyle('left','-999em');
                                                el2.status = 'hide';
                                                el2.setStyle('z-index',12001);
                                            }
                                        });

                                    }
                                }); // fin de boucle level.each
                                el.setStyle('z-index',15000);
                                el.showMaxiCK(dureein);
                            }

                        });

                    } else {
                        el.addEvent('mouseover',function() {
                            if (testoverflow == '1') this.testOverflowMaxiCK(menuID);
                            el.setStyle('z-index',15000);
                            this.showMaxiCK(dureein);

                        });

                        el.addEvent('mouseleave',function() {
                            el.setStyle('z-index',12001);
                            this.hideMaxiCK(dureeout);

                        });
                    }
                    
                }
            });
        }
			
    });

    if (MooTools.version > '1.12' ) Element.extend = Element.implement;

       
    Element.extend({

        testOverflowMaxiCK: function(menuID) {
            var limite = document.getElement('#'+menuID).offsetWidth + document.getElement('#'+menuID).getLeft();


            if (this.hasClass('parent')) {
                var largeur = this.conteneur.mw + 180;
                if (this.hasClass('level1')) largeur = this.conteneur.mw;

                var positionx = this.getLeft() + largeur;

                if (positionx > limite) {
                    this.getElement('div.floatck').addClass('fixRight');
                    this.setStyle('z-index','15000');
                }
				
            }

        },

               
        createFxMaxiCK: function() {
			
            var myTransition = new Fx.Transition(Fx.Transitions[this.transition][this.ease]);
            if (this.hasClass('level1') && this.orientation != '1')
            {
                if ((this.opentype == 'slide' && this.direction == 'normal') || (this.opentype == 'open' && this.direction == 'inverse')) {
                    this.maxiFxCK2 = new Fx.Tween(this.slideconteneur, {
                        property: 'margin-top',
                        duration:this.duree,
                        transition: myTransition
                    });
                    this.maxiFxCK2.set(-this.conteneur.mh);
                }
                this.maxiFxCK = new Fx.Tween(this.conteneur, {
                    property:'height',
                    duration:this.duree,
                    transition: myTransition
                });
                
            } else {
                if ((this.opentype == 'slide' && this.direction == 'normal') || (this.opentype == 'open' && this.direction == 'inverse')) {
                    this.maxiFxCK2 = new Fx.Tween(this.slideconteneur, {
                        property: 'margin-left',
                        duration:this.duree,
                        transition: myTransition
                    });

                    this.maxiFxCK2.set(this.conteneur.mw);
                }
                this.maxiFxCK = new Fx.Tween(this.conteneur, {
                    property:'width',
                    duration:this.duree,
                    transition: myTransition
                });
            //this.maxiFxCK.set(0);
            }

            if (this.useopacity == '1') {
                this.maxiOpacityCK = new Fx.Tween(this.conteneur, {
                    property: 'opacity', 
                    duration:this.duree
                });
                // this.maxiOpacityCK.set(0);
            }
            

            
            // to show the active subitems
            if (this.showactivesubitems == '1' && this.conteneur.getElement('.active')) {
                this.conteneur.setStyle('left', 'auto');
				this.conteneur.setStyle('opacity', '1');
            } else {
                this.maxiFxCK.set(0);
				if (this.useopacity == '1') this.maxiOpacityCK.set(0);
                this.conteneur.setStyle('left', '-999em');
            }

            
				
            animComp = function(){
                if (this.status == 'hide')
                {
                    this.conteneur.setStyle('left', '-999em');
                    this.hidding = 0;
                    this.setStyle('z-index',this.zindex);
                    if (this.opentype == 'slide' && this.hasClass('level1') && this.orientation != '1') this.slideconteneur.setStyle('margin-top','0');
                    if (this.opentype == 'slide' && (!this.hasClass('level1') || this.orientation != '1')) this.slideconteneur.setStyle('margin-left','0');

                }
                this.showing = 0;
                this.conteneur.setStyle('overflow', '');
					
            }
            this.maxiFxCK.addEvent ('onComplete', animComp.bind(this));
            if ((this.opentype == 'slide' && this.direction == 'normal') || (this.opentype == 'open' && this.direction == 'inverse')) this.maxiFxCK2.addEvent ('onComplete', animComp.bind(this));

        },
			
        showMaxiCK: function(timeout) {
            clearTimeout (this.timeout);
            this.addClass('sfhover');
            this.status = 'show';
            clearTimeout (this.timeout);
            if (timeout)
            {
                this.timeout = setTimeout (this.animMaxiCK.bind(this), timeout);
            }else{
                this.animMaxiCK();
            }
        },
			
        hideMaxiCK: function(timeout) {
            this.status = 'hide';
            this.removeClass('sfhover');
            clearTimeout (this.timeout);
            if (timeout)
            {
                this.timeout = setTimeout (this.animMaxiCK.bind(this), timeout);
            }else{
                this.animMaxiCK();
            }
        },

        animMaxiCK: function() {

            if ((this.status == 'hide' && this.conteneur.style.left != 'auto') || (this.status == 'show' && this.conteneur.style.left == 'auto' && !this.hidding) ) return;
					
            this.conteneur.setStyle('overflow', 'hidden');
            if (this.status == 'show') {
                this.hidding = 0;
            }
            if (this.status == 'hide')
            {
                this.hidding = 1;
                this.showing = 0;
                this.maxiFxCK.cancel();
                if ((this.opentype == 'slide' && this.direction == 'normal') || (this.opentype == 'open' && this.direction == 'inverse'))
                    this.maxiFxCK2.cancel();
					
                if (this.hasClass('level1') && this.orientation != '1') {
                    this.maxiFxCK.start(this.conteneur.offsetHeight,0);
                    if ((this.opentype == 'slide' && this.direction == 'normal') || (this.opentype == 'open' && this.direction == 'inverse')) {
                        this.maxiFxCK2.start(0,-this.conteneur.offsetHeight);
                    } 
                } else {
                    this.maxiFxCK.start(this.conteneur.offsetWidth,0);
                    if ((this.opentype == 'slide' && this.direction == 'normal') || (this.opentype == 'open' && this.direction == 'inverse')) {
                        this.maxiFxCK2.start(0, -this.conteneur.offsetWidth);
                    } 
                }
                if (this.useopacity == '1') {
                    this.maxiOpacityCK.cancel();
                    this.maxiOpacityCK.start(1,0);
                }
                

            } else {
                this.showing = 1;
                this.conteneur.setStyle('left', 'auto');
                this.maxiFxCK.cancel();
                if ((this.opentype == 'slide' && this.direction == 'normal') || (this.opentype == 'open' && this.direction == 'inverse'))
                    this.maxiFxCK2.cancel();
                if (this.hasClass('level1') && this.orientation != '1') {
                    this.maxiFxCK.start(this.conteneur.offsetHeight,this.conteneur.mh);
                    if ((this.opentype == 'slide' && this.direction == 'normal') || (this.opentype == 'open' && this.direction == 'inverse')) {
                        this.maxiFxCK2.start(-this.conteneur.mh,0);
                    } 
                } else {
                    this.maxiFxCK.start(this.conteneur.offsetWidth,this.conteneur.mw);
                    if ((this.opentype == 'slide' && this.direction == 'normal') || (this.opentype == 'open' && this.direction == 'inverse')) {
                        this.maxiFxCK2.start(-this.conteneur.mw,0);
                    }
                }
                if (this.useopacity == '1') {
                    this.maxiOpacityCK.cancel();
                    this.maxiOpacityCK.start(0,1);
                }
                
            }
				

        }
    });

    DropdownMaxiMenu.implement(new Options); //ajoute les options utilisateur � la class

		
/*Window.onDomReady(function() {new DropdownMenu($E('ul.maximenuck'),{
                  //mooTransition : 'Quad',
			               //mooTransition : 'Cubic',
			               //mooTransition : 'Quart',
			               //mooTransition : 'Quint',
			               //mooTransition : 'Pow',
			               //mooTransition : 'Expo',
			               //mooTransition : 'Circ',
			               mooTransition : 'Sine',
			               //mooTransition : 'Back',
			               //mooTransition : 'Bounce',
			               //mooTransition : 'Elastic',

			               mooEase : 'easeIn',
                                       //mooEase : 'easeOut',
                                       //mooEase : 'easeInOut',
                                       
                                       mooDuree : 500
                                       })
                                       });*/

}