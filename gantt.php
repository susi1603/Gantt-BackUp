<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Ronic - Dashboard</title>
	<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
	<link rel="stylesheet" href="https://unpkg.com/bulma@0.8.0/css/bulma.min.css" />

	<script>

		$(()=>getGants());

		$(()=>mouseEvents());

		var GANTT;
		var SCALE       = 20;
		var dragStart	= "";
		var dragFinish	= "";
		var dividerPos 	= "";
		var idLeft   	= "";
		var idRight  	= "";
		var isPressed	= false;
		var MONTHS 		= ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"];

		function getGants(){
			$.post( "___Scripts/_php/gantt.php")
				.done(
					function( data ) {
						GANTT = JSON.parse(data);
						//console.log(GANTT);
						loadGantt();
					}
			);
		}

		function loadGantt(){
			var output 			= "";
			var outputGraphics 	= "";
			$("#projects").html("");
			$("#CanvasGraphicRows").html("");

			createCalendarHeader(2020);

			$.each(GANTT,function(index,project){

				output += `<div class="project">
							<div class="project-header"
								id="PRJ_`+project.idProject+`"
								startDate="`+project.startDate+`"
								onclick="selectRow(this)">
								<div class="project-row">
									<div class="row-icon"><i class="fa fa-minus-square"></i></div>
									<div class="row-title">`+project.projectName+`</div>
									<div class="row-progress">`+project.progress+`%</div>
									<div class="row-end-date">`+formatDate(project.endDate)+`</div>
									<div class="row-start-date">`+formatDate(project.startDate)+`</div>
								</div>
							</div>`;

				outputGraphics += `	<div class="ProjectGraphicContainer"
									id="CNT_PRJ_`+project.idProject+`">
										<div class="ProjectName"
											id="GRPH_PRJ_`+project.idProject+`"
											width   ="`+getWidth(project.endDate, project.startDate)+`"

											style="width:`+getWidth(project.endDate, project.startDate)+`px; left:`+daysFrom(project.startDate)+`px;">

											<div class="ProjectProgress" style="width:`+getProgressWidth(project.endDate, project.startDate, project.progress)+`px"></div>
											<div class="itemStart" onmousedown="action(this,0)"></div>
											<div class="itemLabel" onmousedown="action(this,1)"></div>
											<div class="itemEnd"   onmousedown="action(this,2)"></div>

										</div>
									</div>`;

				output += `<div class="project-activities" show="true">`;

				$.each(project.activities,function(index,activity){
					output += `<div class="activity ">
									<div class="activity-header"
										id="ACT_`+activity.idTask+`"
										startDate="`+activity.startDate+`"
										onclick = "selectRow(this)">
										<div class="activity-row">
											<div class="row-id">`+activity.idTask+`</div>
											<div class="row-icon"><i class="fa fa-minus-square"></i></div>
											<div class="row-title">`+activity.taskName+`</div>
											<div class="row-progress">`+activity.progress+`%</div>
											<div class="row-end-date">`+formatDate(activity.endDate)+`</div>
											<div class="row-start-date">`+formatDate(activity.startDate)+`</div>
											<div class="row-assigned-to">`+activity.assignedTo+`</div>
										</div>
								</div>`;

					outputGraphics += `	<div class="MainTaskGraphicContainer"
											id="CNT_ACT_`+activity.idTask+`">
											<div class="MainTask "
											id="GRPH_ACT_`+activity.idTask+`"
											style="width:`+getWidth(activity.endDate, activity.startDate)+`px; left:`+daysFrom(activity.startDate)+`px;">
											<div class="MainTaskProgress" style="width:`+getProgressWidth(activity.endDate, 	activity.startDate, activity.progress)+`px"></div>
										</div>
									</div>`;

					output += `<div class="project-activity-tasks" show="true">`;

					$.each(activity.tasks,function(index,task){
						output += `		<div class="task-row"
										id="TSK_`+task.idTask+`"
										startDate="`+task.startDate+`"
										onclick = "selectRow(this)">
										<div class="row-id">`+task.idTask+`</div>
										<div class="row-icon"><i class="fa fa-check-circle-o" style="color:Goldenrod"></i></div>
										<div class="row-title">`+task.taskName+`</div>
										<div class="row-progress">`+task.progress+`%</div>
										<div class="row-end-date">`+formatDate(task.endDate)+`</div>
										<div class="row-start-date">`+formatDate(task.startDate)+`</div>
										<div class="row-assigned-to">`+task.assignedTo+`</div>
								   </div>`;

					outputGraphics += `	<div class="TaskGraphicContainer"
												id="CNT_TSK_`+task.idTask+`">
												<div class="taskRow "
													id="GRPH_TSK_`+task.idTask+`"
													style="width:`+getWidth(task.endDate, task.startDate)+`px; left:`+daysFrom(task.startDate)+`px;">
													<div class="TaskProgress" style="width:`+getProgressWidth(task.endDate, task.startDate, task.progress)+`px"></div>											</div>											</div>`;

					})
					output += `</div></div>`;


				})
				output += `</div></div>`;


			})
			output += `</div></div>`;

			$("#projects").html(output);
			$("#CanvasGraphicRows").html(outputGraphics);
			createGrid();
		}

		var itemPressed = false;
		var itemData    ={}

		function endDrag(){
			itemPressed   = false;
		}

		function action(element, action){
			var data      = {};
			data.position = $(element).parent().position().left;
			data.size     = parseInt($(element).parent().attr("width"));
			data.id       = $(element).parent().attr("id");
			data.action   = parseInt(action);
			itemData      = data;
			itemPressed   = true;

			/*console.log($(element).parent().attr("position"));*/
		}

		function selectRow(element){
			if (idLeft!= ""){
				$("#"+idLeft).removeClass("gantt-row-selected");
			}
			if (idRight!= ""){
				$("#"+idRight).removeClass("gantt-row-selected");
			}

			var rowid  = $(element).attr("id");
			var grphid = "CNT_"+rowid;

			$(element).addClass("gantt-row-selected");
			$("#"+grphid).addClass("gantt-row-selected");
			var offset = daysFrom($(element).attr("startDate"));
			$("#CanvasRigth").animate( {scrollLeft:(offset)}, 500);

			idRight = grphid;
			idLeft  = rowid;

		}

		function createGrid(){
			var gridBlock = "";
			var width     = parseInt($(".ProjectGraphicContainer").css("width"));
			var times     = Math.floor(width/SCALE);

			for (var i = 1; i < times ; i++){
				gridBlock += `<div class="grid-item" style="width:`+SCALE+`px;"></div>`;
			}

			$(".ProjectGraphicContainer,.MainTaskGraphicContainer,.TaskGraphicContainer").append(gridBlock);
		}

		function getWidth(end, start){
					/* var endDate		= Date.parse(new Date(end));
					var startDate	= Date.parse(new Date(start));

					var days = endDate - startDate;

					var longitud = ((days/86400000) * SCALE) + SCALE;

					return longitud; */
					return (((new Date(end).getTime() - new Date(start).getTime()) / (1000*3600*24)+1)*SCALE);
			}

		function getProgressWidth(end, start, progress){
					var progress_r = getWidth(end, start)*(progress/100);
					return progress_r;
			}

		function daysFrom(start){
			return ((new Date(start).getTime() - new Date("2020-01-01").getTime()) / (1000*3600*24)-1)*SCALE;
		}

		function formatDate(date){
			var formattedDate = "19/Apr";
			var newDate = new Date(date);
			return newDate.getDate()+"/"+MONTHS[newDate.getMonth()];
		}

		function createCalendarHeader(year){
			var monthBlock		= "";
			var dayBlock		= "";

			for(var i = 0; i < 12 ; i++){
				var daysInMonth		= new Date(year, i+1, 0).getDate();
				var w 			=	daysInMonth*SCALE;
				var monthName	=	MONTHS[i];

				monthBlock += `<div class="monthHeader" style="width:`+ w +`px; "> `+monthName+`</div>`;

				for(var j = 1 ; j < daysInMonth+1 ; j++ ){
					dayBlock 	+= `<div class="dayHeader"  style="width:`+ SCALE + `px ">`+j+`</div>`;
				}
			}
				$("#CalendarHeaderMonth").html(monthBlock);
				$("#CalendarHeaderDay").html(dayBlock);
			}

		function hideShow(element){
			var parent			= $(element).parent('div');
			var brother			= parent.children('div:nth-child(2)');

			if($(brother).attr("show") == 'true'){
				brother.css( "display", "none" );
				brother.attr("show","false");
			}else {
				brother.css( "display", "block" );
				brother.attr("show","true");
			}

		}

   		function getPosition(startDate){
			 var day 	= new Date(startDate).getDate();
			 var month 	= new Date(startDate).getMonth();
			 var  rmonth =MONTHS[month];
			 var idDate = (day+rmonth);
		}

		function mouseEvents(){
				$("#CanvasLeft").scroll(function(e){
					$("#CanvasRigth").scrollTop($("#CanvasLeft").scrollTop())
				});

				$("#CanvasRigth").scroll(function(e){
					$("#CanvasLeft").scrollTop($("#CanvasRigth").scrollTop())
				})

				$( "#CanvasDivider" )
  				.mouseup(function(e) {
					isPressed = false;
				})
  				.mousedown(function(e) {
					isPressed	= true;
					dragStart	= e.pageX;
					dividerPos	= $("#CanvasDivider").position();
  				});

			/*	$("html")
					.mousemove(function(e){
						var dif			= dragFinish - dragStart;
					if(isPressed){
						$("#CanvasDivider").css("left", +(dividerPos.left+dif) + "px");
						$("#CanvasLeft").css("width", +(dividerPos.left+dif) + "px");
						$("#CanvasRigth").css("left", +(dividerPos.left+dif+15) + "px");
					}
					dragFinish	= e.pageX;
					});*/

				$("html")
					.mousemove(function(e){
						var dif			= dragFinish - dragStart;
					if(isPressed){
						$("#CanvasDivider").css("left", +(dividerPos.left+dif) + "px");
						$("#CanvasLeft").css("width", +(dividerPos.left+dif) + "px");
						$("#CanvasRigth").css("left", +(dividerPos.left+dif+15) + "px");
					}

					if(itemPressed){

						switch(itemData.action){
							case 0 :
								var newPosition = itemData.position+dif;
								var newSize 	= itemData.size-dif;

								$("#"+itemData.id).css("left",newPosition+"px");
								$("#"+itemData.id).attr("position",newPosition);
								$("#"+itemData.id).css("width",newSize+"px");
								$("#"+itemData.id).attr("size",newSize);

								break;
							case 1 :
								var newSize 	= itemData.size;
								var newPosition = itemData.position+dif;

								$("#"+itemData.id).css("left",newPosition+"px");
								$("#"+itemData.id).attr("position",newPosition);

								break;
							case 2 :
								var newSize 	= itemData.size+dif;
								var newPosition = itemData.position;

								$("#"+itemData.id).css("width",newSize+"px");
								$("#"+itemData.id).attr("size",newSize);

								break;
						}
					}
					dragFinish	= e.pageX;
				})
				.mouseup(function(e){
					itemPressed	= false;
				})
				.mousedown(function (e){
					e.preventDefault();
				});
		}

	</script>

	<style>

		:root{
			--SCALE: 20px;
		}

		.itemStart{
			position: absolute;
			width: 20px;
			height: inherit;
			border-radius: 10px;
			background-color: pink;
			cursor:w-resize;
			left:0px;
		}

		.itemLabel{
			position: absolute;
			height: inherit;
			border-radius: 10px;
			background-color: yellow;
			cursor:all-scroll;
			top: 1px;
			left: 20px;
			bottom: 0px;
			right: 20px;
			text-align: center;
			font-size: 10px;
			font-weight: bold;
			vertical-align: middle;
			/*white-space: nowrap;
			text-overflow: ellipsis;
			overflow: hidden;*/
		}

		.itemEnd{
			position: absolute;
			width: 20px;
			height: inherit;
			border-radius: 10px;
			background-color: deepskyblue;
			cursor:e-resize;
			float: right;
			right: 0px;
		}

		.gantt-section{
			border: dashed;
			position: fixed;
			width: 95%;
			height: 95%;
			margin: 20px;
		}

		.monthHeader{
			display: table-cell;
			font-size: 10px;
			border: 1px solid black;
			height: 100%;
			text-align: center;
			float: left; !important
			text-align: center;
			border-collapse: collapse;
		}

		.dayHeader{
			font-size: 10px;
			border: 1px solid black;
			text-align: center;
			float: left; !important
		}

		#CalendarHeaderMonth{
			width: 100%;
			height: 60%;
			white-space: nowrap;
			border-collapse: collapse;
		}

		#CalendarHeaderDay{
			width: 100%;
			height: 40%;
		}

		#CanvasGraphicHeader{
			width: 10000px;
			height: 40px;
			top: 0;
			background: white;
		}

		.project{
			padding-left: 20px;
			padding-rigth: 20px;
			font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", Verdana, "sans-serif";
			color: gray;
			font-size: 12px;
			font-weight: lighter;
		}

		.project-header{
			border-bottom: gray 2px solid;
			font-weight: bold;
			color: black;
		}

		.project-row{
			clear: both;
			height: 25px;
			padding-top: 8px;
		}

		.activity-row{
			clear: both;
			height: 25px;
			padding-top: 8px;
		}

		.activity-row:hover {
			background: #F2F2F2;
			cursor: pointer;

		}

		.task-row{
			clear: both;
			height: 25px;
			padding-top: 8px;
		}

		.task-row:hover{
			background: #F2F2F2;
			cursor: pointer;
		}

		.project-row:hover {
			background: #F2F2F2;
			cursor: pointer;

		}

		.project-activity-tasks{
			padding-left: 20px;

		}

		.row-id{
			width: 25px;
			float: left;
			text-align: right;

		}

		.row-icon{
			width: 20px;
			text-align: center;
			float: left;
		}

		.row-title{
			float: left;
			width: auto;

		}

		.row-assigned-to{
			float: right;
			width: 130px;
			padding-right: 5px;
			text-align: right;
		}

		.row-start-date{
			float: right;
			width: 60px;
			text-align: center;
		}

		.row-end-date{
			float: right;
			width: 60px;
			text-align: center;
		}

		.row-progress{
			float: right;
			width: 60px;
			text-align: center;
		}

		.gantt-row-selected{
			background-color: lightgray !important;
		}

		.ProjectGraphicContainer{
			position: relative;
			height: 27px;
			background-color: rgba(255, 255, 255, 0.8);
 			border: 0.5px solid rgba(0, 0, 0, 0.2);
			width: 10000px;
			display: flex;
   			align-items: center;
		}

		.ProjectName{
			position: absolute;
			left: 0px;
			border: 1px solid green;
			background-color: rgba(0,255,0,0.20);
			height: 15px;
			width: 300px;
			border-radius: 10px;
			display: table-cell;
			vertical-align: middle;
		}

		.ProjectProgress{
			position: absolute;
			left: 0px;
			background-color: rgba(0,255,0,0.60);
			height: 15px;
			width: 300px;
			border-radius: 10px;
			display: table-cell;
			vertical-align: middle;
		}

		.MainTaskGraphicContainer{
			position: relative;
			height: 27px;
			background-color: rgba(255, 255, 255, 0.8);
 			border: 0.5px solid rgba(0, 0, 0, 0.2);
			width: 10000px;
			display: flex;
   			align-items: center;
		}

		.MainTask{
			position: absolute;
			left: 0px;
			border: 1px solid blue;
			background-color: rgba(0,0,255,0.20);
			height: 15px;
			width: 300px;
			border-radius: 10px;
			display: table-cell;
			vertical-align: middle;
		}

		.MainTaskProgress{
			position: absolute;
			left: 0px;
			background-color: rgba(0,0,255,0.60);
			height: 15px;
			width: 300px;
			border-radius: 10px;
			display: table-cell;
			vertical-align: middle;
		}

		.TaskGraphicContainer{
			position: relative;
			height: 27px;
			background-color: rgba(255, 255, 255, 0.5);
 			border: 0.5px solid rgba(0, 0, 0, 0.2);
			width: 10000px;
			display: flex;
   			align-items: center;
		}

		.taskRow{
			position: absolute;
			left: 0px;
			border: 1px solid red;
			background-color: rgba(255,0,0,0.20);
			height: 15px;
			width: 300px;
			border-radius: 10px;
			display: table-cell;
			vertical-align: middle;
		}

		.TaskProgress{
			position: absolute;
			left: 0px;
			background-color: rgba(255,0,0,0.60);
			height: 15px;
			width: 300px;
			border-radius: 10px;
			display: table-cell;
			vertical-align: middle;
		}

		.gantt-headerGraphic{
			padding: 0px;
			margin: 0px;
			height: 100%;
			width 100%;
			border-left: solid 1px;
		}

		.gantt-header-months{
			padding: 0px;
			margin: 0px;
			clear: both;
		}

		.gantt-header-month{
			float:left;
			border-right: solid 1px rgba(0,0,0,0.2);
			text-align: center;
			border-collapse: collapse;
			font-size: 12px;


		}

		.gantt-header-day{
			float:left;
			border-right: solid 1px rgba(0,0,0,0.2);
			border-top: solid 1px rgba(0,0,0,0.2);
			text-align: center;
			font-size: 8px;
			border-collapse: collapse;
		}

		.grid-item {
  			background-color: inherit;
			border-right: 1px solid rgba(0, 0, 0, 0.2);
			display: flex;
			height: 27px;
}

		th{
			font-size: 12px;
			text-overflow: ellipsis;
		}

		#CanvasLeft{
			position: absolute;
			top: 0px;
			left: 0px;
			height: 100%;
			width: 700px;
			min-width: 100px;
			overflow-y: scroll;
		}

		#CanvasDivider{
			position: absolute;
			top: 0px;
			left: 700px;
			bottom: 0px;
			border-left: 1px solid #D8D8D8;
			border-right: 1px solid #D8D8D8;
			width: 15px;
			text-align: center;
			vertical-align: middle;
			background: white;
			box-shadow: 2px 2px 5px 0px rgba(0,0,0,0.3);
		}

		#CanvasRigth{
			position: absolute;
			top: 0px;
			left: 715px;
			right: 0px;
			height: 100%;
			overflow-x: scroll;
			overflow-y: scroll;
		}

		#DividerPicker{
			position: absolute;
			top: 50%;
			width: 60%;
			height: 20px;
			border-left: 2px solid #808080;
			border-right: 2px solid #808080;
			margin: 2px;
			cursor: grab;
		}

		body{
			height: 90%;
			width: 90%;
			overflow: hidden;
		}
	</style>

</head>

<body>

	<div class= "gantt-section">
		<div id="Canvas">

			<div id="CanvasLeft">
				<table class="table is-fullwidth" style="height: 40px; margin: 0; border-bottom: 1px solid #D8D8D8;" >
					<tr class="th">
						  <th width="30"></th>
						  <th width="30"></th>
						  <th width="30"></th>
						  <th>Name</th>
						  <th width="140">Assigned</th>
						  <th width="80">Start Date</th>
						  <th width="80">End Date</th>
						  <th width="60">Progress</th>
					</tr>
				</table>
				<div id="projects"></div>
			</div>


			<div id="CanvasDivider">
				<div id="DividerPicker"></div>
			</div>


			<div id="CanvasRigth">
				<div id="CanvasGraphicHeader" >
				<div id="CalendarHeaderMonth"></div>
				<div id="CalendarHeaderDay"	></div>
				</div>
				<div id="CanvasGraphicRows"></div>
			</div>

		</div>
	</div>





</body>
</html>
