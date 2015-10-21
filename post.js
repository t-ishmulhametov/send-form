/*

 	101 - msg send successful
	100 - msg send not successful 
	50 - input captcha not successful 
	51 - you need show captcha image
	52 - this msg is send

 */



$(function(){

	$("form.bznbs-postmail").find("button").click(function(){
		var $form=$(this).closest('form');
		var val={};

		$form.find("input").each(function(ind,el){
			if ($(el).attr('data-necessarily')=="*" && $(el).val()==""){
				$(el).addClass('is-error');
			}else if (($(el).attr('type')=='radio' || $(el).attr('type')=='checkbox') && $(el).prop("checked")){
				val[$(el).attr('name')]=$(el).val();
				$(el).removeClass('is-error');
			}	
			else if ($(el).attr('type')=='text'){
				val[$(el).attr('name')]=$(el).val();
				$(el).removeClass('is-error');
			}
		});

		$form.find("textarea").each(function(ind,el){
			if ($(el).attr('data-necessarily')=="*" && $(el).val()==""){
				$(el).addClass('is-error');
			}else {
				val[$(el).attr('name')]=$(el).val();
				$(el).removeClass('is-error');
			}
		});
		$form.find("select").each(function(ind,el){
			val[$(el).attr('name')]=$(el).val();
		});
		console.log(val);
		if (!$form.find(".is-error").length){

			$.post($form.attr('action'), val, function(data){
				
				$form.find("*[data-role='frame frame--form']").removeClass('is-visible');
				$form.find("*[data-role='frame frame--captcha']").removeClass('is-visible');
				$form.find("*[data-role='frame frame--thankyou']").removeClass('is-visible');
				
				if (data==51 || data==50){
					$form.find("*[data-role='frame frame--captcha']").addClass('is-visible');
					$form.find("*[data-role='frame frame--captcha']").find("img[data-role='captcha-image']").attr('src',$form.attr('action')+"?act=51&"+new Date().getTime());
					
				}else {
					$form.find("*[data-role='frame frame--thankyou']").addClass('is-visible');
				}
				console.log(data);
			});
		}
		
		return false;
	});

	
});


