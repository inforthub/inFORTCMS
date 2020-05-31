<?php
/**
 * NotificationList
 *
 * @version    1.0
 * @package    control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class NotificationList extends TElement
{
    public function __construct($param)
    {
        parent::__construct('ul');
        
        try
        {
            TTransaction::open('communication');
            // load the notifications to the logged user
            $system_notifications = SystemNotification::where('checked', '=', 'N')
                                                      ->where('dt_message', '<=', date('Y-m-d 23:59:59'))
                                                      ->where('system_user_to_id', '=', TSession::getValue('userid'))
                                                      ->orderBy('id', 'desc')
                                                      ->load();
            
            if ($param['theme'] == 'theme3')
            {
                $this->class = 'dropdown-menu';
                
                $a = new TElement('a');
                $a->{'class'} = "dropdown-toggle";
                $a->{'data-toggle'}="dropdown";
                $a->{'href'} = "#";
                
                $a->add( TElement::tag('i',    '', array('class'=>"far fa-bell fa-fw")) );
                $a->add( TElement::tag('span', count($system_notifications), array('class'=>"label label-warning")) );
                $a->show();
                
                $li_master = new TElement('li');
                $ul_wrapper = new TElement('ul');
                $ul_wrapper->{'class'} = 'menu';
                $li_master->add($ul_wrapper);
                
                parent::add( TElement::tag('li', _t('Notifications'), ['class'=>'header']));
                parent::add($li_master);
                
                foreach ($system_notifications as $system_notification)
                {
                    $date    = $this->getShortPastTime($system_notification->dt_message);
                    $subject = $system_notification->subject;
                    $icon    = $system_notification->icon ? $system_notification->icon : 'far fa-bell text-aqua';
                    $icon    = str_replace( 'fa:', 'fa fa-', $icon);
                    $icon    = str_replace( 'far:', 'far fa-', $icon);
                    $icon    = str_replace( 'fas:', 'fas fa-', $icon);
                    
                    $li  = new TElement('li');
                    $a   = new TElement('a');
                    $div = new TElement('div');
                    
                    $a->href = (new TAction(['SystemNotificationFormView', 'onView'], ['id' => $system_notification->id]))->serialize();
                    $a->generator = 'adianti';
                    $li->add($a);
                    
                    $i = new TElement('i');
                    $i->{'class'} = $icon;
                    $a->add($i);
                    $a->add($subject);
                    $a->add( TElement::tag('span', $date, array('class' => 'pull-right text-muted small') ) );
                    
                    $ul_wrapper->add($li);
                }
                
                parent::add(TElement::tag('li', TElement::tag('a', _t('View all'),
                    ['href'=> (new TAction(['SystemNotificationList', 'onReload']))->serialize(),
                     'generator'=>'adianti'] ), ['class'=>'footer'] ));
            }
            else if ($param['theme'] == 'theme4')
            {
                $this->class = 'dropdown-menu';
                
                $a = new TElement('a');
                $a->{'class'} = "dropdown-toggle";
                $a->{'data-toggle'}="dropdown";
                $a->{'href'} = "#";
                
                $a->add( TElement::tag('i',    'notifications', array('class'=>"material-icons")) );
                $a->add( TElement::tag('span', count($system_notifications), array('class'=>"label-count")) );
                $a->show();
                
                $li_master = new TElement('li');
                $ul_wrapper = new TElement('ul');
                $ul_wrapper->{'class'} = 'menu';
                $ul_wrapper->{'style'} = 'list-style:none';
                $li_master->{'class'} = 'body';
                $li_master->add($ul_wrapper);
                
                parent::add( TElement::tag('li', _t('Notifications'), ['class'=>'header']));
                parent::add($li_master);
                
                foreach ($system_notifications as $system_notification)
                {
                    $date    = $this->getShortPastTime($system_notification->dt_message);
                    $subject = $system_notification->subject;
                    $icon    = $system_notification->icon ? $system_notification->icon : 'far fa-bell text-aqua';
                    $icon    = str_replace( 'fa:', 'fa fa-', $icon);
                    $icon    = str_replace( 'far:', 'far fa-', $icon);
                    $icon    = str_replace( 'fas:', 'fas fa-', $icon);
                    
                    $li  = new TElement('li');
                    $a   = new TElement('a');
                    $div = new TElement('div');
                    $div2= new TElement('div');
                    
                    $a->href = (new TAction(['SystemNotificationFormView', 'onView'], ['id' => $system_notification->id]))->serialize();
                    $a->class = 'waves-effect waves-block';
                    $a->generator = 'adianti';
                    $li->add($a);
                    
                    $div->{'class'} = 'icon-circle';
                    $div->{'style'} = 'background:whitesmoke';
                    $div2->{'class'} = 'menu-info';
                    
                    $div->add( TElement::tag('i', '', array('class' => $icon) ) );
                    
                    $h4 = new TElement('h4');
                    $h4->add( $subject );
                    
                    $div2->add($h4);
                    $a->add($div);
                    $a->add($div2);
                    
                    $p = new TElement('p');
                    $p->add( TElement::tag('i', 'access_time', ['class' => 'material-icons']) );
                    $p->add( $date );
                    
                    $div2->add( $p );
                    $ul_wrapper->add($li);
                }
                
                parent::add(TElement::tag('li', TElement::tag('a', _t('View all'),
                    ['href'=> (new TAction(['SystemNotificationList', 'onReload']))->serialize(),
                     'generator'=>'adianti'] ), ['class'=>'footer'] ));
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    public function getShortPastTime($from)
    {
        $to = date('Y-m-d H:i:s');
        $start_date = new DateTime($from);
        $since_start = $start_date->diff(new DateTime($to));
        if ($since_start->y > 0)
            return $since_start->y.' '._t('years').' ';
        if ($since_start->m > 0)
            return $since_start->m.' '._t('months').' ';
        if ($since_start->d > 0)
            return $since_start->d.' '._t('days').' ';
        if ($since_start->h > 0)
            return $since_start->h.' '._t('hours').' ';
        if ($since_start->i > 0)
            return $since_start->i.' '._t('minutes').' ';
        if ($since_start->s > 0)
            return $since_start->s.' '._t('seconds').' ';    
    }
}
