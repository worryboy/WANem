#/****************************************************************************/
#/*                              TCS WANem                                   */

#/****************************************************************************/
#/*                   COPYRIGHT (c) 2007 TCS All Rights Reserved             */
#/*                                                                          */
#/*                                                                          */
#/* WANem is a WAN emulation tool Conceptualized and developed by Innovation */
#/* LAB TCS. We thank the open source community as we have taken the inspira */
#/* tion from the Netem, the open source network emulator.                   */
#/*                                                                          */
#/*                                                                          */
#/****************************************************************************/
#/****************************************************************************/
#/*   Author       : Manoj Nambiar, TCS Innovation Lab Performance Engg.     */

#/*   Date         : March 2007                                              */
#/*   Synopsis     : reset_disc.sh                                           */
#/*   Description  :                                                         */
#/*                                                                          */
#/*   Modifications:                                                         */
#/****************************************************************************/
#/*                                                                          */
export PATH=$PATH:/usr/local/sbin:/sbin:/bin:/usr/bin
#$1/kill_disc.sh $1
awk -f $1/ri.awk -v intf=$2 $1/input.dsc > $1/input.dsc.bkp1
mv -f $1/input.dsc $1/input.dsc.bkp
mv -f $1/input.dsc.bkp1 $1/input.dsc
rm -f $1/input.dsc.bkp1
chown -R www-data $1
chgrp -R www-data $1
$1/disconnect.sh $1 &
