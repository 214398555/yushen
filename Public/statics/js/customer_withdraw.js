//时间选择
function selecttime(flag){
	if(flag==1){
		var endTime = $("#countTimeend").val();
		if(endTime != ""){
			WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',maxDate:endTime});
		}else{
			WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'});
		}
	}else{
		var startTime = $("#countTimestart").val();
		if(startTime != ""){
			WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',minDate:startTime});
		}else{
			WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'});
		}	
	}
}

$(document).ready(function() {
	/************提现申请*****************/
	//审核标签切换
	$('p button').click(function(){
		var id = $(this).attr('id');
		window.location.href = "{:U('Customer/withdraw')}?id="+id;
		$(this).addClass('btn-info');
	});
	
	//顶部搜索条件
	$('form button').click(function(){
		var btn_val = $(this).text();
		if(btn_val=='清空条件'){
			$('#form1')[0].reset();
			return false;
		} else if(btn_val=='搜索') {
			if($("input[name='keyword']").val()!='' && $('.caiwu').val()=='请选择'){
				alert('请选择搜索类型');return false;
			}
			if(($("#countTimestart").val()!='' && $("#countTimeend").val()=='') ||　($("#countTimestart").val()=='' && $("#countTimeend").val()!='')) {
				alert('请同时选择开始和结束时间');
				return false;
			}
			$('#form1').submit();
		} else if(btn_val=='导出') {
			$('#form1').attr('action', "{:U('Customer/csv')}");
			$('#form1').submit();
		}
	});
	
	//更改列显示
	$('#select_th').multiselect({
		onChange:function(element, checked){			
			var vv = element.val()
			
			if(checked == false) {
				$("#table1").find("tr th:nth-child("+vv+")").hide();
				$("#table1").find("tr td:nth-child("+vv+")").hide();
			} else {
				$("#table1").find("tr th:nth-child("+vv+")").show();
				$("#table1").find("tr td:nth-child("+vv+")").show();
			}
			//console.log(element);
		}
	});
	//审核
	$('td select').change(function(){
		var obj = $(this);
		var id_status = obj.attr('name');
		var status = obj.val();
		
	
		if(status>0) {
			$.post("{:U('Customer/updateStatus')}",{id_status:id_status,status:status}, function(data){
				if(data == 'fail') {
					obj.parents('td').html('未通过审核');
					return false;
				} else {
					if(data.status=='30' || data.status=='40'){
						obj.hide();
					} else {
						obj.attr('name',data.id+'_'+data.status);
						//obj.find("option[value='0']").attr("selected",true);//没有效果，使用下面刷新页面
						window.location.reload();
					}
					obj.prev('span').html(data.msg);
					return false;
				}
			});
		}
	});
	//备注更新
	$('td span button').click(function(){
		var id = $(this).attr('alt');
		var remark = $(this).parents('td').find('textarea').val();
		if(remark==''){
			$(".alert-warning").show();
			setTimeout('$(".alert-warning").hide()',1000);
			$(this).parents('td').find('textarea').focus();
			return false;
		}
		if(id && remark) {
			$.post("{:U('Customer/updateRemark')}", {id:id, remark: remark},function(data){
				if(data == 'ok'){
					$(".alert-success").show();
					setTimeout('$(".alert-success").hide()',1000);
					return false;
				}　else if(data == 'false') {
					$(".alert-warning").show();
					setTimeout('$(".alert-warning").hide()',1000);
					$(this).parents('td').find('textarea').focus();
					return false;
				} else if(data == 'fail') {
					$(".alert-danger").show();
					setTimeout("$('.alert-danger').hide()",1000);
					return false;
				}
				
			});
		}
		
	});
   
});