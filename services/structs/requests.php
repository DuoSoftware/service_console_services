<?php
	class ScheduleRequest{
		public $RefId;
		public $RefType;
		public $OperationCode;
		public $TimeStamp;
		public $TimeStampReadable;
		public $ControlParameters;
		public $Parameters;
		public $ScheduleParameters;
	}


	class CommonResponse{
		public $Exception;
		public $CustomMessage;
		public $IsSuccess;
		public $Result;
	}
?>