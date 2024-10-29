<?php

namespace AFormsEats\Infra;

class FormMapper 
{
    const TABLE = "wqeforms";
    protected $wpdb;

    public function __construct($wpdb) 
    {
        $this->wpdb = $wpdb;
    }

    public function getSampleForm($author) 
    {
        $json = <<<EOT
        {
            "id": 0,
            "title": "Japanese Izakaya Menu",
            "navigator": "horizontal",
            "doConfirm": true,
            "thanksUrl": "",
            "detailItems": [
                {
                    "id": 1,
                    "type": "Group",
                    "image": "",
                    "name": "Sashimi",
                    "note": [],
                    "products": [
                        {
                            "id": 1,
                            "type": "Product",
                            "image": "",
                            "name": "Sashimi Moriawase 3 pieces",
                            "note": [],
                            "price": 1200,
                            "ribbons": {
                                "RECOMMENDED": true
                            }
                        },
                        {
                            "id": 5,
                            "type": "Product",
                            "image": "",
                            "name": "Sashimi Moriawase 5 pieces",
                            "note": [],
                            "price": 2500,
                            "ribbons": {}
                        },
                        {
                            "id": 6,
                            "type": "Product",
                            "image": "",
                            "name": "Katsuo no Tataki",
                            "note": [],
                            "price": 580,
                            "ribbons": {
                                "SALE": true
                            }
                        },
                        {
                            "id": 7,
                            "type": "Product",
                            "image": "",
                            "name": "Maguro no Sashimi",
                            "note": [],
                            "price": 580,
                            "ribbons": {}
                        },
                        {
                            "id": 9,
                            "type": "Product",
                            "image": "",
                            "name": "Shake no Sashimi",
                            "note": [],
                            "price": 580,
                            "ribbons": {}
                        },
                        {
                            "id": 8,
                            "type": "Product",
                            "image": "",
                            "name": "Tako no Sashimi",
                            "note": [],
                            "price": 480,
                            "ribbons": {}
                        }
                    ]
                },
                {
                    "id": 2,
                    "type": "Group",
                    "image": "",
                    "name": "Gyokai",
                    "note": [],
                    "products": [
                        {
                            "id": 2,
                            "type": "Product",
                            "image": "",
                            "name": "Tempura Moriawase",
                            "note": [],
                            "price": 1500,
                            "ribbons": {}
                        },
                        {
                            "id": 4,
                            "type": "Product",
                            "image": "",
                            "name": "Hokke no Hiraki",
                            "note": [],
                            "price": 2000,
                            "ribbons": {}
                        },
                        {
                            "id": 10,
                            "type": "Product",
                            "image": "",
                            "name": "Kawaebi no Karaage",
                            "note": [],
                            "price": 780,
                            "ribbons": {}
                        }
                    ]
                },
                {
                    "id": 6,
                    "type": "Group",
                    "image": "",
                    "name": "Niku",
                    "note": [],
                    "products": []
                },
                {
                    "id": 3,
                    "type": "Group",
                    "image": "",
                    "name": "Domburi",
                    "note": [],
                    "products": [
                        {
                            "id": 3,
                            "type": "Product",
                            "image": "",
                            "name": "Ume-Chaduke",
                            "note": [],
                            "price": 580,
                            "ribbons": {}
                        },
                        {
                            "id": 11,
                            "type": "Product",
                            "image": "",
                            "name": "Sake-Chaduke",
                            "note": [],
                            "price": 480,
                            "ribbons": {}
                        }
                    ]
                },
                {
                    "id": 4,
                    "type": "PriceWatcher",
                    "lower": null,
                    "lowerIncluded": true,
                    "higher": 3000,
                    "higherIncluded": false,
                    "labels": {
                        "small-order": true
                    }
                },
                {
                    "id": 5,
                    "type": "Auto",
                    "category": "",
                    "name": "Delivery Fee",
                    "price": 1000,
                    "depends": {
                        "small-order": true
                    },
                    "quantity": -1
                }
            ],
            "attrItems": [
                {
                    "id": 1,
                    "type": "Name",
                    "required": true,
                    "name": "Your Name",
                    "note": [],
                    "divided": false,
                    "pattern": "none"
                },
                {
                    "id": 2,
                    "type": "Tel",
                    "required": true,
                    "name": "Phone Number",
                    "note": [],
                    "divided": false
                },
                {
                    "id": 3,
                    "type": "Address",
                    "required": true,
                    "name": "Destination",
                    "note": [],
                    "autoFill": "yubinbango"
                },
                {
                    "id": 4,
                    "type": "Text",
                    "required": false,
                    "name": "Message",
                    "note": [],
                    "multiline": true,
                    "size": "normal"
                }
            ],
            "mail": {
                "subject": "Thank you for Order",
                "fromAddress": "info@example.com",
                "fromName": "AForms Eats Devel",
                "notifyTo": "info@example.com",
                "textBody": "Thank you for Order."
            }
        }
EOT;
        $form = json_decode($json, false);
        $form->author = $author;
        $form->modified = time();
        
        list($row, $_unused) = $this->objectToRow($form);
        $row['author_id'] = $author->id;
        $row['author_name'] = $author->name;
        $row['id'] = $form->id;

        return $this->rowToObject($row);
    }

    public function createTable() 
    {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $table = $this->wpdb->prefix . self::TABLE;
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table ("
             . "  id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, "
             . "  title varchar(100) NOT NULL, "
             . "  author bigint(20) NOT NULL, "
             . "  modified int(11) NOT NULL, "
             . "  content mediumtext NOT NULL "
             . ") ".$charset_collate;
        
        dbDelta($sql);
    }
    
    // Dropping-table moved to aforms.php

    protected function rowToObject($row) 
    {
        if ($row['author_id']) {
            $author = new \stdClass();
            $author->id = (int)$row['author_id'];
            $author->name = $row['author_name'];
        } else {
            $author = null;
        }

        $form = new \stdClass();
        $form->id = (int)$row['id'];
        $form->title = $row['title'];
        $form->author = $author;
        $form->modified = (int)$row['modified'];
        
        $content = json_decode($row['content'], false);
        
        // migiration
        if (! property_exists($content, 'doConfirm')) {
            $content->doConfirm = true;
        }
        if (! property_exists($content, 'navigator')) {
            $content->navigator = 'horizontal';
        }
        if (! property_exists($content, 'thanksUrl')) {
            $content->thanksUrl = '';
        }
        foreach ($content->detailItems as $di) {
            if (($di->type == 'Selector' || $di->type == 'Auto') && ! property_exists($di, 'quantity')) {
                // default quantity is 1-fixed.
                $di->quantity = -1;
            }
            if ($di->type == 'Group') {
                if (! property_exists($di, 'visible')) {
                    $di->visible = true;
                }
                foreach ($di->products as $product) {
                    if (! property_exists($product, 'taxRate')) {
                        $product->taxRate = null;
                    }
                    if (! property_exists($product, 'state')) {
                        if (! property_exists($product, 'visible')) {
                            $product->state = 'effective';
                        } else {
                            $product->state = ($product->visible) ? 'effective' : 'hidden';
                        }
                    }
                }
            }
            if ($di->type == 'Auto' && ! property_exists($di, 'taxRate')) {
                $di->taxRate = null;
            }
            if ($di->type == 'Selector') {
                foreach ($di->options as $option) {
                    if (! property_exists($option, 'ribbons')) {
                        $option->ribbons = (object)array();
                    }
                }
            }
        }
        foreach ($content->attrItems as $ai) {
            if ($ai->type == 'Name' && ! property_exists($ai, 'pattern')) {
                $ai->pattern = 'none';
            }
            if (($ai->type == 'Radio' || $ai->type == 'Checkbox') && ! property_exists($ai, 'initialValue')) {
                $ai->initialValue = '';
            }
            if ($ai->type == 'Address' && ! property_exists($ai, 'autoFill')) {
                $ai->autoFill = 'none';
            }
            if ($ai->type == 'Text' && ! property_exists($ai, 'placeholder')) {
                $ai->placeholder = '';
            }
        }
        if (! property_exists($content->mail, 'alignReturnPath')) {
            $content->mail->alignReturnPath = false;
        }

        $form->navigator = $content->navigator;
        $form->doConfirm = $content->doConfirm;
        $form->thanksUrl = $content->thanksUrl;
        $form->detailItems = $content->detailItems;
        $form->attrItems = $content->attrItems;
        $form->mail = $content->mail;

        return $form;
    }

    public function findById($id) 
    {
        $table = $this->wpdb->prefix . self::TABLE;
        $sql = "SELECT f.*, u.ID AS author_id, u.user_nicename AS author_name "
             . "FROM $table f LEFT JOIN ".$this->wpdb->users." u ON f.author = u.ID "
             . "WHERE f.id = %d";
        $row = $this->wpdb->get_row($this->wpdb->prepare($sql, $id), ARRAY_A);
        if (! $row) {
            return null;
        }

        return $this->rowToObject($row);
    }

    public function findByTitle($title) 
    {
        $table = $this->wpdb->prefix . self::TABLE;
        $sql = "SELECT f.*, u.ID AS author_id, u.user_nicename AS author_name "
             . "FROM $table f LEFT JOIN ".$this->wpdb->users." u ON f.author = u.ID "
             . "WHERE f.id = %s";
        $row = $this->wpdb->get_row($this->wpdb->prepare($sql, $title), ARRAY_A);
        if (! $row) {
            return null;
        }

        return $this->rowToObject($row);
    }

    public function getList() 
    {
        $table = $this->wpdb->prefix . self::TABLE;
        $sql = "SELECT f.*, u.ID AS author_id, u.user_nicename AS author_name "
             . "FROM $table f LEFT JOIN ".$this->wpdb->users." u ON f.author = u.ID "
             . "ORDER BY f.id DESC ";
        $rows = $this->wpdb->get_results($sql, ARRAY_A);

        $rv = array();
        foreach ($rows as $row) {
            $rv[] = $this->rowToObject($row);
        }

        return $rv;
    }

    public function getListFor($userId) 
    {
        $table = $this->wpdb->prefix . self::TABLE;
        $sql = "SELECT f.*, u.ID AS author_id, u.user_nicename AS author_name "
             . "FROM $table f LEFT JOIN ".$this->wpdb->users." u ON f.author = u.ID "
             . "WHERE f.author = %d "
             . "ORDER BY f.id DESC ";
        $rows = $this->wpdb->get_results($this->wpdb->prepare($sql, $userId), ARRAY_A);

        $rv = array();
        foreach ($rows as $row) {
            $rv[] = $this->rowToObject($row);
        }

        return $rv;
    }

    protected function objectToRow($form) 
    {
        $data = array(
            'title' => $form->title, 
            'author' => ($form->author) ? $form->author->id : null, 
            'modified' => $form->modified, 
            'content' => json_encode($form)
        );
        $format = array('%s', '%d', '%d', '%s');
        return array($data, $format);
    }

    public function add($form) 
    {
        $table = $this->wpdb->prefix . self::TABLE;
        list($data, $format) = $this->objectToRow($form);
        $this->wpdb->insert($table, $data, $format);
        $form->id = $this->wpdb->insert_id;
        return $form;
    }

    public function sync($form) 
    {
        $table = $this->wpdb->prefix . self::TABLE;
        list($data, $format) = $this->objectToRow($form);
        $this->wpdb->update($table, $data, array('id' => $form->id), $format, array('%d'));
    }

    public function remove($form) 
    {
        $table = $this->wpdb->prefix . self::TABLE;
        $this->wpdb->delete($table, array('id' => $form->id), array('%d'));
    }

    public function listIds() 
    {
        $table = $this->wpdb->prefix . self::TABLE;
        
        $sql = "SELECT id FROM $table";
        $vars = $this->wpdb->get_col($sql);

        $rv = array();
        foreach ($vars as $v) {
            $rv[] = (int)$v;
        }
        return $rv;
    }

    public function listIdsFor($userId) 
    {
        $table = $this->wpdb->prefix . self::TABLE;
        
        $sql = "SELECT id FROM $table WHERE author = %d";
        $vars = $this->wpdb->get_col($this->wpdb->prepare($sql, $userId));

        $rv = array();
        foreach ($vars as $v) {
            $rv[] = (int)$v;
        }
        return $rv;
    }
}