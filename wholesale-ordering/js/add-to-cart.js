jQuery(document).ready(function($) {

	var ajaxurl = localvars.ajax_url;
	var carturl = localvars.cart_url;
	var currency_symbol = localvars.currency_symbol;
	var thousand_separator = localvars.thousand_separator;
	var decimal_separator = localvars.decimal_separator;
	var decimal_decimals = localvars.decimal_decimals;
	var currency_pos = localvars.currency_pos;
	var price_display_suffix = localvars.price_display_suffix;
	var gclicked = 0;
	var glob_clicked = 0;
	var vartable_ajax = localvars.vartable_ajax;
	var $fragment_refresh = '';
	var count = 0;
	var numofadded = 0;
	var requests_done = 0;

	var formdata = new Array;

	//$('.variations_form table.variations, .variations_form .quantity, .woocommerce-variation-add-to-cart.variations_button').remove();

	vartable_init();
	console.log('Loaded');
	// Move variations table
	jQuery('table.variations').prependTo('.wholesale-ordering-table-wrapper');
	jQuery('table.variations').css('display','block');
	jQuery('.wholesale_multiple_data label').click(function(){
		
	});
	jQuery('.variations_form').on('woocommerce_variation_has_changed', function () {
		console.log("Has Changed 2");
		setTimeout(function() { 
		jQuery('.is-next').trigger("mouseover");
		jQuery('.splide__list li').eq(1).trigger("mouseover");
		},3000);
	});
	$supports_html5_storage = ('sessionStorage' in window && window['sessionStorage'] !== null);
	if (vartable_ajax == 1) {
		$fragment_refresh = {
			url: ajaxurl,
			type: 'POST',
			data: { action: 'woocommerce_get_refreshed_fragments' },
			success: function(data) {
				if (data && data.fragments) {

					$.each(data.fragments, function(key, value) {
						$(key).replaceWith(value);
					});

					if ($supports_html5_storage) {
						sessionStorage.setItem("wc_fragments", JSON.stringify(data.fragments));
						sessionStorage.setItem("wc_cart_hash", data.cart_hash);
					}
					console.log('refresh');
					$('body').trigger('wc_fragments_refreshed');
				}
			}
		};
	}

	function vartable_init() {
		if ($("table.wholesale_multiple_data").length > 0) {

			$(document).on('click', '.globalcartbtn:not(.loading)', function(e) {

				e.preventDefault();

				glob_clicked = 1;
				gclicked = 1;
				numofadded = 0;
				requests_done = 0;

				if ($(this).hasClass('loading')) return false; // If its already running then return

				let $this = $(this),
					wrapper = $this.parents('.vartable_gc_wrap'),
					parent = $this.parents('.wholesale-ordering-table-wrapper'),
					pid = $this.attr('data-product_id');
				
				

				$this.addClass('loading disabled').attr('disabled', 'disabled');

				count = 0;
				parent.find("input.order_quantity").each(function(index) { // variation quantity input loop
					if ( $(this).val() > 0 ) { // If quantity greater then 0 
						count = count + 1;
					}
				});

				// If user didn't put any quantity
				if (count == 0) {
					$this.removeClass('loading').addClass('disabled').prop("disabled", true);
					return false;
				}

				var trig = 0;
				parent.find("input.order_quantity").each(function(index) { // variation quantity input loop
					if ( $(this).val() > 0 ) { // If quantity greater then 0 
						formdata = [];
						formdata.length = 0;
						formobj =  $(this).parents('.qty_parent');
						formdata = get_form_data(formobj);
						formdata['product_id'] = pid;
						vartable_request(formdata);

						requests_done = requests_done + 1;
						trig = 1;

					}

				});

				// All requests complete
				$(document).on('vartable_global_add_finished', function() {

					if (count <= 0) {

						glob_clicked = 0;
						gclicked = 0;
						$this.removeClass('loading disabled').prop("disabled", false);
						console.log('finished');
					}

					$('input.order_quantity').val('').trigger('change');
					$('body').trigger('wc_fragments_refreshed');

					if($('.ct-cart-item').length) {
						simulateClick(document.querySelector('.ct-cart-item'));
					} else if($('.header-cart-button').length) {
						$('.header-cart-button').trigger('click'); 
					}

				});

			});

		}
	}

	Number.prototype.formatMoney = function(c, d, t) {
		var n = this,
			c = isNaN(c = Math.abs(c)) ? 2 : c,
			d = d == undefined ? "." : d,
			t = t == undefined ? "," : t,
			s = n < 0 ? "-" : "",
			i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
			j = (j = i.length) > 3 ? j % 3 : 0;
		return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
	};

	function get_price_html(price) {
		price = parseFloat(price).formatMoney(decimal_decimals, decimal_separator, thousand_separator);

		if (currency_pos == 'left') {
			price = currency_symbol + price;
		}
		if (currency_pos == 'right') {
			price = price + currency_symbol;
		}
		if (currency_pos == 'left_space') {
			price = currency_symbol + ' ' + price;
		}
		if (currency_pos == 'right_space') {
			price = price + ' ' + currency_symbol;
		}

		if (price_display_suffix != '') {
			price = price + ' ' + price_display_suffix;
		}

		return price;
	}

	function get_form_data(formobj) {


		formdata['variation_id'] = formobj.find('input.variation_id').val();
		formdata['quantity'] = formobj.find('input[name="order_quantity"]').val();
		formdata['variations'] = formobj.find('input[name="form_vartable_attribute_array"]').val();
		formdata['vartable_ajax'] = '1';
		formdata['cartredirect'] = 'no';

		return formdata;

	}

	function vartable_request(formdata) {

		jQuery.ajaxQueue({
			type: "POST",
			url: ajaxurl,
			data: {
				"action": "add_variation_to_cart",
				"product_id": formdata['product_id'],
				"variation_id": formdata['variation_id'],
				"variations": formdata['variations'],
				"quantity": formdata['quantity'],
				"gift_wrap": ''
			},
			success: function(data) {
				
				console.log('data');
				console.log( data );

				// conditionally perform fragment refresh
				if (formdata['vartable_ajax'] == 1) {
					$.ajax($fragment_refresh);
				}
				
				if ( data.error == true && data.error_message !== undefined && data.error_message != '' ) {
					console.log(data.error_message);
					alert(data.error_message);
				}

				// set counter to track when all requests are done
				count = count - 1;

				if (count <= 0) {
					if (glob_clicked == 1 || gclicked == 1) {
						$('body').trigger('vartable_global_add_finished');
						glob_clicked = 0;
						gclicked = 0;
					}
				}

			},
			error: function(data) {
				console.log('error');
				console.log(data);
			}
		});

	}


	(function($) {

		var ajaxQueue = $({});

		$.ajaxQueue = function(ajaxOpts) {

			var oldComplete = ajaxOpts.complete;

			ajaxQueue.queue(function(next) {

				ajaxOpts.complete = function() {
					if (oldComplete) oldComplete.apply(this, arguments);

					next();
				};

				$.ajax(ajaxOpts);
			});
		};

	})(jQuery);

	
	// $(document).on('input', 'input.order_quantity', function() {

	// 	var valmin = $(this).attr('min');
	// 	var valmax = $(this).attr('max');


	// 	if (typeof valmin === 'undefined' || valmin === null) {
	// 		valmin = 0;
	// 	}
	// 	if (typeof valmax === 'undefined' || valmax === null) {
	// 		valmax = -1;
	// 	}

	// 	if (parseInt($(this).val()) < valmin) {
	// 		$(this).val(valmin);
	// 	}
	// 	if (parseInt($(this).val()) > valmax && valmax != -1) {
	// 		$(this).val(valmax);
	// 	}

	// });


	// Quantity change input
	$(document).on('input', 'input.order_quantity', function() {
		showProductTotal();
	});

	// Total price of one product with multiple variations
	function showProductTotal() {
		let total = 0,
			inputed_count = 0;
		$('.wholesale_multiple_data input.order_quantity').each(function() {
			let $this = $(this),
				parent = $this.parents('.qty_parent'),
				price = parent.find('input.price').val(),
				single_price = price * $this.val();
			
			if($this.val() > 0) {
				inputed_count++;
			}

			total = total + single_price;
		});

		if(inputed_count > 0) {
			$('.vartable_gc_wrap .globalcartbtn').removeClass('disabled disabled').prop('disabled', false);
		} else {
			$('.vartable_gc_wrap .globalcartbtn').addClass('disabled disabled').attr('disabled', 'disabled');
		}

		get_price_html(total);
	}


	let simulateClick = function (elem) {
		// Create our event (with options)
		let evt = new MouseEvent('click', {
			bubbles: true,
			cancelable: true,
			view: window
		});
		// If cancelled, don't dispatch our event
		let canceled = !elem.dispatchEvent(evt);
	};


});