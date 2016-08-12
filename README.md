Original files : https://github.com/Bazaid/tatar-wars-old

Live version available here : http://vitrex-dboor.tk/
###All copyrights of old files goes to Dboor creators.

Goal of this project is to update these awesome files , add more flexibility to them , make them more secured, clean all the mess and useless code, re-write old things with new, add few custom features, fix known / new bugs.

###There is still no actual testers, if you would like to join please contact me via email : b.expire@gmail.com Or Skype : Jakbut

Please report all the bugs you can find, so they can be fixed faster. 
Live version of script will be available soon.

####Please keep in mind that till stable V1.0 Release commits can be done every week or hour , so don't waste so much time cloning up to date source every time we update it, better wait for stable release if you gonna use it in public. Now these files are completely broken and not worth a cent.

#Changelog
- Deleted one backdoor script
- Fixed issues where you can't login
- Improved configs system so you can configurate troops speed ,  trops carry, merchant carry.
- Warehouse and Grannary capacity can be modified.
- Natars spawn time fixed, you can set the value by yourself
- Install improved, now oasis will have resources depending on server speed and capacity multiplier.
- Fixed non static method errors , they won't bother you anymore.
- Remake of mysql class to mysqli so you can run this script on phpversions > 5.4
- Fixed few more errors related to non-static methods.
- Fixed few major issues / left overs after decode with broken syntax.
- Added option to disable / enable email activation after new user registration.
- Added new feature silver coins, now you can enable / disable this system and set up the ratio of silver coins given for player for his actions. (will be hot-fixed and improved later).
- Improved guide system, now rewards and requirements like (wheat donate mission) scales with server speed.
- Improved register, now if you disabled email activation, it won't show that you must activate your account you can straight log in
- Fixed issue with beginners protection time, now it has correct value and can be modified in metadata.php
- Improved cranny , now it's capacity scales with configurations..
- Fixed infinity loop with merchants sending on high speed servers (same as used to be with troops building).
