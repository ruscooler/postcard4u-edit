<?php

class TicketEntity
{
    protected $product_id;
    protected $product_link;
    protected $category_name;
    protected $category_name_ru;
    protected $product_name;
    protected $product_name_ru;
    protected $product_description;
    protected $product_description_ru;
    protected $product_idealfor;
    protected $product_idealfor_ru;
    protected $product_frame;
    protected $product_text_input;
    protected $status;
    protected $photo;

    /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct(array $data) {
        // no id if we're creating
        if(isset($data['product_id'])) {
            $this->product_id = $data['product_id'];
        }

        $this->product_link = $data['product_link'];
        $this->product_name = $data['product_name'];
        $this->product_name_ru = $data['product_name_ru'];
        $this->category_name = $data['category_name'];
        $this->category_name_ru = $data['category_name_ru'];
        $this->product_description = $data['product_description'];
        $this->product_description_ru = $data['product_description_ru'];
        $this->product_idealfor = $data['product_idealfor'];
        $this->product_idealfor_ru = $data['product_idealfor_ru'];
        $this->product_frame = $data['product_frame'];
        $this->product_text_input = $data['product_text_input'];
        $this->status = $data['status'];
        $this->photo = $data['photo'];
    }

    public function getId() {
        return $this->product_id;
    }

    public function getLink() {
        return $this->product_link;
    }

    public function getCatName() {
        return $this->category_name;
    }

    public function getCatNameRu() {
        return $this->category_name_ru;
    }

    public function getName() {
        return $this->product_name;
    }

    public function getNameRu() {
        return $this->product_name_ru;
    }

    public function getProductDesc() {
        return $this->product_description;
    }

    public function getProductDescRu() {
        return $this->product_description_ru;
    }

    public function getIdealFor() {
        return $this->product_idealfor;
    }

    public function getIdealForRu() {
        return $this->product_idealfor_ru;
    }

    public function getFrame() {
        return $this->product_frame;
    }

    public function getTextInput() {
        return $this->product_text_input;
    }
    public function getStatus() {
        return $this->status;
    }
    public function getPhoto() {
        return $this->photo;
    }
}
