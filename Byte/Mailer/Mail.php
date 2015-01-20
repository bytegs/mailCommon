<?php
namespace Byte\Mailer;

class Mail
{
	public function __construct($id = NULL)
	{
		$this->db = new \Easy\PDOW\PDOW();
		if($id!==NULL)
		{
			$this->checkDB();
			$res = $this->db->query("SELECT * FROM `mails` WHERE `id` = ?", array($id));
			if(count($res)!=1)
			{
				throw new \Exception("Mail ID not unique", 1);
				
			}
			$res = $res[0];
			$this->setTo($res["to"]);
			$this->setParam(json_decode($res["varieables"], true));
			$this->setTemplateID($res["templateID"]);
			$this->setStatus($res["status"]);
			$this->id = $id;
		}
	}
	public function setDB()
	{
		$this->db = $db;
	}
	public function checkDB()
	{
		if(!isset($this->db))
		{
			throw new \Exception("No Database Connection Instance set", 1);
		}
	}
	static public function getDB()
	{
		$db = new \Easy\PDOW\PDOW();
		return $db;
	}

	public function setTo($mail)
	{
		$this->to = $mail;
	}
	public function getTo()
	{
		return $this->to;
	}
	public function addParam($param, $name)
	{
		$this->params[$param]=$name;
	}
	public function setParam($array)
	{
		$this->params = $array;
	}
	public function getParam($param = NULL)
	{
		if($param === NULL)
		{
			return $this->params;
		}
		elseif(isset($this->params[$param]))
		{
			return $this->params[$param];
		}
		return false;
	}
	public function setTemplate($name)
	{
		$this->checkDB();
		$res = $this->db->query("SELECT * FROM `mailTemplates` WHERE `name` = ?", array($name));
		if(isset($res[0][0]))
		{
			$this->templateID = $res[0][0];
			$this->body = $res[0]["body"];
			$this->subject = $res[0]["subject"];
			return true;
		}
		return false;
	}
	public function setTemplateID($id)
	{
		$this->checkDB();
		$res = $this->db->query("SELECT * FROM `mailTemplates` WHERE `id` = ?", array($id));
		if(isset($res[0][0]))
		{
			$this->templateID = $res[0][0];
			$this->body = $res[0]["body"];
			$this->subject = $res[0]["subject"];
			return true;
		}
		return false;
	}
	public function getTemplateID()
	{
		return $this->templateID;
	}
	public function getBody()
	{
		return $this->render("body");
	}
	public function getSubject()
	{
		return $this->render("subject");
	}
	private function setStatus($status)
	{
		$this->status = $status;
	}
	public function getStatus()
	{
		return $this->status;
	}
	private function render($typ = "body")
	{
		$loader = new \Twig_Loader_Array(array(
		    'body' => $this->body,
		    'subject' => $this->subject
		));
		$twig = new \Twig_Environment($loader);
		return $twig->render($typ, $this->params);
	}
	public function send()
	{
		$this->checkDB();
		$sql = "INSERT INTO `mails`(`templateID`, `varieables`, `to`, `status`) VALUES (?, ?, ?, ?)";
		$this->id = $this->db->insertID($sql, array($this->templateID, json_encode($this->params), $this->to, "waiting"));
		return $this->id;
	}
	public function getID()
	{
		return $this->id;
	}
}