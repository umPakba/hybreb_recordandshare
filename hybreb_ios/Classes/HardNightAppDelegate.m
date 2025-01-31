//
//  hybrebAppDelegate.m
//  hybreb
//
//  Created by Jakob Lahmer on 22.08.11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import "HardNightAppDelegate.h"
#import "ListViewController.h"
#import "RecordViewController.h"
#import "Reachability.h"

@implementation HardNightAppDelegate

@synthesize window;
@synthesize tabBarController;


#pragma mark -
#pragma mark Application lifecycle

- (BOOL)application:(UIApplication *)application didFinishLaunchingWithOptions:(NSDictionary *)launchOptions {    
    
    // Override point for customization after application launch.

    [self checkOnlineStatus];
    
	// Set the tab bar controller as the window's root view controller and display.
    tabBarController.delegate = self;
    self.window.rootViewController = self.tabBarController;
    [self.window makeKeyAndVisible];

    return YES;
}

- (void)checkOnlineStatus {
    Reachability *reachability = [Reachability reachabilityWithHostName:@"www.hardnight.tv"];
    NetworkStatus internetStatus = [reachability currentReachabilityStatus];
    
    if ((internetStatus != ReachableViaWiFi) && (internetStatus != ReachableViaWWAN))
    {
        NSLog(@"no internet availiable");
        UIAlertView *myAlert = [[UIAlertView alloc] initWithTitle:@"No Internet Connection" message:@"You require an internet connection via WiFi or cellular network." delegate:self cancelButtonTitle:@"Ok" otherButtonTitles:nil];
        [myAlert show];
        [myAlert release];
    }
}

- (BOOL)application:(UIApplication *)application handleOpenURL:(NSURL *)url {
	NSLog(@"i am called");
	
	// call handleOpenURL in RecordViewController (first view controller in tabbarcontroller)
	controller = (RecordViewController *)[self.tabBarController.viewControllers objectAtIndex:0];

	return [[controller facebook] handleOpenURL:url];
}


- (void)applicationWillResignActive:(UIApplication *)application {
    /*
     Sent when the application is about to move from active to inactive state. This can occur for certain types of temporary interruptions (such as an incoming phone call or SMS message) or when the user quits the application and it begins the transition to the background state.
     Use this method to pause ongoing tasks, disable timers, and throttle down OpenGL ES frame rates. Games should use this method to pause the game.
     */
}


- (void)applicationDidEnterBackground:(UIApplication *)application {
    /*
     Use this method to release shared resources, save user data, invalidate timers, and store enough application state information to restore your application to its current state in case it is terminated later. 
     If your application supports background execution, called instead of applicationWillTerminate: when the user quits.
     */
}


- (void)applicationWillEnterForeground:(UIApplication *)application {
    /*
     Called as part of  transition from the background to the inactive state: here you can undo many of the changes made on entering the background.
     */
}


- (void)applicationDidBecomeActive:(UIApplication *)application {
    /*
     Restart any tasks that were paused (or not yet started) while the application was inactive. If the application was previously in the background, optionally refresh the user interface.
     */
}


- (void)applicationWillTerminate:(UIApplication *)application {
    /*
     Called when the application is about to terminate.
     See also applicationDidEnterBackground:.
     */
}


#pragma mark -
#pragma mark UITabBarControllerDelegate methods

// Optional UITabBarControllerDelegate method.
- (void)tabBarController:(UITabBarController *)tabBarController2 didSelectViewController:(UIViewController *)viewController {
    if (tabBarController2.selectedIndex == 1) {
        NSLog(@"Show tab selected. loading view...");
        ListViewController* listView = (ListViewController *)viewController;
        
        [listView loadData];
    }
}

/*
// Optional UITabBarControllerDelegate method.
- (void)tabBarController:(UITabBarController *)tabBarController didEndCustomizingViewControllers:(NSArray *)viewControllers changed:(BOOL)changed {
}
*/


#pragma mark -
#pragma mark Memory management

- (void)applicationDidReceiveMemoryWarning:(UIApplication *)application {
    /*
     Free up as much memory as possible by purging cached data objects that can be recreated (or reloaded from disk) later.
     */
}


- (void)dealloc {
    [tabBarController release];
    [window release];
    [super dealloc];
}

@end

