<?php
namespace AFormsEats\Infra;
class WpSession 
{
    protected $newcapsys;

    public function __construct() 
    {
        $this->newcapsys = get_option(\AFormsEatsWrap::NEACAPSYS_KEY, '<undefined>');
        $this->setupNewcapsys();
    }

    public function setupNewcapsys() 
    {
        if ($this->newcapsys === '<undefined>') {
            // enable newcapsys
            $admin = get_role('administrator');
            $caps = array(
                'aformseats_read_forms',   'aformseats_read_others_forms', 
                'aformseats_write_forms',  'aformseats_write_others_forms', 
                'aformseats_read_orders',  'aformseats_read_others_orders', 
                'aformseats_write_orders', 'aformseats_write_others_orders'
            );
            foreach ($caps as $c) {
                $admin->add_cap($c, true);
            }
            update_option(\AFormsEatsWrap::NEACAPSYS_KEY, false);
            $this->newcapsys = false;
        }
    }

    public function setNewcapsys($enabled) 
    {
        update_option(\AFormsEatsWrap::NEACAPSYS_KEY, $enabled);
        $this->newcapsys = $enabled;
    }

    public function isLoggedIn()
    {
        return is_user_logged_in();
    }
    public function isAdmin() 
    {
        return current_user_can('manage_options');
    }

    public function getUser() 
    {
        $user = wp_get_current_user();
        $rv = new \stdClass();
        $rv->id = $user->ID;
        $rv->name = $user->data->display_name;
        return $rv;
    }

    protected function hasCap($cap, ...$args) 
    {
        if ($this->newcapsys) {
            return current_user_can($cap, ...$args);
        } else {
            return current_user_can('manage_options');
        }
    }

    public function canReadForm($form) 
    {
        return $this->hasCap('aformseats_read_form', $form);
    }

    public function canReadForms($withOthers) 
    {
        return $this->hasCap($withOthers ? 'aformseats_read_others_forms' : 'aformseats_read_forms');
    }

    public function canWriteForm($form) 
    {
        return $this->hasCap('aformseats_write_form', $form);
    }

    public function canWriteForms($withOthers) 
    {
        return $this->hasCap($withOthers ? 'aformseats_write_others_forms' : 'aformseats_write_forms');
    }

    public function canReadOrder($order, $form) 
    {
        return $this->hasCap('aformseats_read_order', $order, $form);
    }

    public function canReadOrders($withOthers) 
    {
        return $this->hasCap($withOthers ? 'aformseats_read_others_orders' : 'aformseats_read_orders');
    }

    public function canWriteOrder($order, $form) 
    {
        return $this->hasCap('aformseats_write_order', $order, $form);
    }

    public function canWriteOrders($withOthers) 
    {
        return $this->hasCap($withOthers ? 'aformseats_write_others_orders' : 'aformseats_write_orders');
    }

    public function mapMetaCap($caps, $cap, $userId, $args) 
    {
        $doFilter = false;

        switch ($cap) {
            case 'aformseats_read_form': 
                $caps[] = 'aformseats_read_forms';
                if (is_array($args) && count($args) > 0) {
                    $form = $args[0];
                    if (! $form->author || $form->author->id != $userId) {
                        $caps[] = 'aformseats_read_others_forms';
                    } 
                }
                $doFilter = true;
                break;
            case 'aformseats_write_form': 
                unset($caps[$cap]);
                $caps[] = 'aformseats_write_forms';
                if (is_array($args) && count($args) > 0) {
                    $form = $args[0];
                    if (! $form->author || $form->author->id != $userId) {
                        $caps[] = 'aformseats_write_others_forms';
                    }
                }
                $doFilter = true;
                break;
            case 'aformseats_read_order': 
                unset($caps[$cap]);
                $caps[] = 'aformseats_read_orders';
                if (is_array($args) && count($args) > 1) {
                    $order = $args[0];
                    $form = $args[1];  // can be null
                    if ($form && $order->formId != $form->id) {
                        $caps[] = 'do_not_allow';
                    }
                    if (! $form || ! $form->author ||  $form->author->id != $userId) {
                        $caps[] = 'aformseats_read_others_orders';
                    }
                }
                $doFilter = true;
                break;
            case 'aformseats_write_order': 
                unset($caps[$cap]);
                $caps[] = 'aformseats_write_orders';
                if (is_array($args) && count($args) > 1) {
                    $order = $args[0];
                    $form = $args[1];  // can be null
                    if ($form && $order->formId != $form->id) {
                        $caps[] = 'do_not_allow';
                    }
                    if (! $form || ! $form->author || $form->author->id != $userId) {
                        $caps[] = 'aformseats_write_others_orders';
                    }
                }
                $doFilter = true;
                break;
        }
        
        if ($doFilter) {
            // remove meta cap itself.
            $newCaps = array();
            foreach ($caps as $c) {
                if ($cap != $c) {
                    $newCaps[] = $c;
                }
            }
            return $newCaps;
        } else {
            return $caps;
        }
    }
}