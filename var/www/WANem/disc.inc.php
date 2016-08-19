<?
/****************************************************************************/
/*                              TCS WANem                                   */                 
/****************************************************************************/
/*                   COPYRIGHT (c) 2007 TCS All Rights Reserved             */
/* 
/* 
/* WANem is a WAN emulation tool Conceptualized and developed by Innovation */
/* LAB TCS. We thank the open source community as we have taken the inspira */
/* tion from the Netem, the open source network emulator.The GUI is also    */
/* a modified version of Netem GUI developed by British Telecom where new   */
/* features are added and the GUI is expanded.                              */
/*                                                                          */
/*                                                                          */
/****************************************************************************/
/****************************************************************************/
/*   Author       : Manoj Nambiar, TCS Innovation Lab Performance Engg.                                             
/*   Date         : March 2007                                              */
/*   Synopsis     :                                                         */
/*   Description  :                                                         */
/*                                                                          */
/*   Modifications:                                                         */
/****************************************************************************/
/*                                                                          */

//Class which keeps the dosconnect params specified in GUI

class disc_object {
var $idl_type;
var $idl_timer;
var $idl_disc_timer;
var $rnd_type;
var $rnd_mttf_lo;
var $rnd_mttf_hi;
var $rnd_mttr_lo;
var $rnd_mttr_hi;
var $rcd_type;
var $rcd_mttf_lo;
var $rcd_mttf_hi;
var $rcd_mttr_lo;
var $rcd_mttr_hi;
}
?>
