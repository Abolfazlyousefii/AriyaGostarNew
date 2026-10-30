$(document).ready(function(){

$('#login-with-code-form').submit(function(e){

e.preventDefault();

let form=$(this);

$.ajax({
url: form.attr('action'),
type:'POST',
data:new FormData(this),
contentType:false,
processData:false,

success:function(data){

console.log(data);

if(data.redirect){
window.location.href=data.redirect;
return;
}

toastr.error('مسیر انتقال دریافت نشد');

},

error:function(xhr){
console.log(xhr.responseText);
toastr.error('خطا در ارسال');
}

});

});

});
