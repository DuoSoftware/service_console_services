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

	class CEBNotifierResponse{
		public $name;
		public $type;
		public $data;
	}

	class ObjectStoreResponse{
		public $IsSuccess;
		public $Message;
		public $Stack;
		public $Data;
		public $Transaction;
		public $TransactionID;
		public $Extras;
	}
?>