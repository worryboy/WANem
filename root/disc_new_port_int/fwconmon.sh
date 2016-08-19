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
#/*   Synopsis     : fwconmon.sh                                             */
#/*   Description  :                                                         */
#/*                                                                          */
#/*   Modifications:                                                         */
#/****************************************************************************/
#/*                                                                          */

export LD_LIBRARY_PATH=/usr/local/lib
modprobe ip_conntrack
COUNTER=0
while [  $COUNTER -eq 0 ]; do
	echo "---------------------------------------"
	date
	echo "---------------------------------------"
	iptables -L -v --line-numbers  
	conntrack -L | grep tcp | grep -v 192.168.140.149 | grep ESTABLISHED 
	sleep 2;
done

