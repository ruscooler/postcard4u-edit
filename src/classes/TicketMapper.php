<?php

class TicketMapper extends Mapper
{
    public function getTickets() {
        $sql = "SELECT product_id, 
                product_name, 
                category_name, 
                product_link, 
                status
                from products  
                where photo=0
                order by status, 
                category_name";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new TicketEntity($row);
        }
        return $results;
    }
    public function getTicketById($ticket_id) {
        $sql = "SELECT *
            from products 
            where product_id = :ticket_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["ticket_id" => $ticket_id]);

        if($result) {
            return new TicketEntity($stmt->fetch());
        }

    }
    public function save(TicketEntity $ticket) {
        $sql = "update products 
                set product_name_ru=:product_name_ru, 
                category_name_ru=:category_name_ru, 
                product_description_ru=:product_description_ru, 
                product_idealfor_ru=:product_idealfor_ru, 
                status=:status 
                WHERE product_id=:product_id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "product_id" => $ticket->getId(),
            "product_name_ru" => $ticket->getNameRu(),
            "category_name_ru" => $ticket->getCatNameRu(),
            "product_description_ru" => $ticket->getProductDescRu(),
            "product_idealfor_ru" => $ticket->getIdealForRu(),
            "status" => $ticket->getStatus(),
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }
    public function saveLine(TicketEntity $ticket) {
        $sql = "update products 
                set product_frame=:product_frame, 
                product_text_input=:product_text_input 
                WHERE product_id=:product_id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "product_id" => $ticket->getId(),
            "product_text_input" => $ticket->getTextInput(),
            "product_frame" => $ticket->getFrame(),
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }
    public function edit(TicketEntity $ticket) {
        $sql = "update tickets set title=:title, 
            linkfrom=:linkfrom, linkto=:linkto
            where id=:ticket_id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "title" => $ticket->getTitle(),
            "linkfrom" => $ticket->getLinkfrom(),
            "linkto" => $ticket->getLinkto(),
            "ticket_id" => $ticket->getId(),
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }
    public function delete($ticket_id) {
        $sql = "delete from tickets where id = :ticket_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["ticket_id" => $ticket_id]);

        if(!$result) {
            throw new Exception("could not delete record");
        }

    }
    public function getTicketByLinkfrom($linkfrom) {
        $sql = "SELECT *
            from products 
            where product_id = :linkfrom";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["linkfrom" => $linkfrom]);
        $result_array = $stmt->fetch();

        if($result_array) {
            return $result_array["linkto"];
        }

    }
    public function checkUser($id) {
        $sql = "SELECT id
            from users 
            where id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["id" => $id]);
        $result_array = $stmt->fetch();
        if($result_array) {
            setcookie("id_user", $result_array["id"], time()+3600*24*14,'/');
            # echo $_COOKIE["id_user"];exit();
            return true;
        }
        else return false;

    }
    public function autorization($user) {
        $sql = "SELECT id, login, password
            from users 
            where login = :login
            and password = :password";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["login" => $user["login"], "password" => $user["password"]]);
        $result_array = $stmt->fetch();
        if($result_array) {
            setcookie("id_user", $result_array["id"], time()+3600*24*14,'/');
            # echo $_COOKIE["id_user"];exit();
            return true;
        }
        else return false;

    }
}
