### System skeleton
![System skeleton](https://raw.github.com/liuchuangww/image/master/user/userSystemSkeleton.png)
### Manifest
![Manifest](https://raw.github.com/liuchuangww/image/master/user/userSystemArchitecture.png)
### Controller and Action
**Front**
* RegisterController
  * indexAction
      * Description: Register page
      * Template: register-idex.phtml
  * activityAction
      * Description: Activate account
      * Template: register-activity.phtml
  * completeAction
      * Description: Complete register information
      * Template: register-activity.phtml
* PasswordController
  * indexAction
      * Description: Change password page
      * Template:password-index.phtml
  * findAction
      * Description: find password page
      * Template:password-find.phtml
  * activityAction
      * Description: Activate account
      * Template: register-activity.phtml
  * processAction
      * Description: Change password according to find password page
      * Template:password-process.phtml
* LoginController
  * indexAction
      * Description: Login page
      * Template:login-index.phtml
  * logoutAction
      * Description: Logout
      * Template: none
* EmailController
  * indexAction
      * Description: Change email page
      * Template:email-index.phtml
  * processAction
      * Description: Verify change email link, change email
      * Template: email-process.phtml
  * sendCompleteAction
      * Description: Display send email result information
      * Template: email-send-complete.phtml
* AccountController
  * indexAction
      * Description: Entries to operate account action
      * Template:account-index.phtml
* ProfileController
  * indexAction
      * Description: User home page
      * Template: profile-index.phtml
  * editAction
      * Description: Edit profile page
      * Template: profile-edit.phtml
  * activityAction
      * Description: Activity page
      * Template: profile-activity.phtml
* SettingController
  * privacyAction
      * Description: User set privacy
      * Template: setting-privacy.phtml
  * preferenceAction
      * Description: User preference setting
      * Template: setting-preference.phtml
* TimelineController
  * indexAction
      * Description: User timeline page
      * Template: setting-privacy.phtml

**Admin**
* AccountController
  * indexAction
      * Description: List all user account
      * Template:account-index.phtml
  * normalAction
      * Description: Normal user list
      * Template: account-normal.phtml
  * manageAction
      * Description: Operator select user
      * Template: none
  * pendingAction
      * Description: Pending user list
      * Template: account-pending
  * bannedAction
      * Description:Banned user list
      * Template: account-benned.pthml
  * deleteAction
      * Description:Delete user
      * Template: none
  * banAction
      * Description: Ban user
      * Template: none
  * activeAction
      * Description: Active user
      * Template: none
  * deletedAction
      * Description: All deleted user list
      * Template: account-deleted.phtml
  * clearAction
      * Description: Clear deleted user
      * Template: account-deleted.phtml
  * roleAction
      * Description: Assign role
      * Template: account-role.phtml
  * passwordAction
      * Description: Change password
      * Template: account-password.phtml
  * viewAction
      * Description: View user account and profile
      * Template: account-view.phtml
  * editAction
      * Description: Edit user account and profile
      * Template: account-edit.phtml
* SettingController
  * privacyAction
      * Description: Set privacy
      * Template: setting-privacy.phtml
  * avatarAction
      * Description: Set avatar
      * Template: account-avatar.phtml
* FormController
  * registerAction
      * Description: Preview register form
      * Template:form-register.phtml
  * profilePerfectAction
      * Description: Preview profile perfect form
      * Template: form-profile-perfect.phtml
  * profileEditAction
      * Description: Preview profile edit form
      * Template:form-profile-edit.phtml
  * profileEditAction
      * Description: Preview profile edit form
      * Template:form-profile-edit.phtml

* PluginController
  * timelineAction
      * Description: Set timeline
      * Template: plugin-timeline.phtml
  * activityAction
      * Description: Set activity
      * Template: plugin-activity.phtml
  * quickLinkAction
      * Description: Set quick link
      * Template: plugin-quick-link.phtml
* NotificationController
  * indexAction
      * Description: New notification
      * Template: notification-index.phtml
* ProfileController
  * listAction
      * Description: List profile fied
      * Template:profile-list.phtml
  * dressupAction
      * Description: Set profile home page
      * Template:profile-dressup.phtml
* StaticsController
  * indexAction
      * Description: Static
      * Template: static-index.phtml

### Api

```
/**
 * User APIs
 *
 * + Meta operations
 *   - getMeta([$type])                                     // Get meta list of user, type: account, profile, extra - extra profile non-structured
 *
 * + User operations
 *   + Bind
 *   - bind($id[, $field])                                  // Bind current user
 *   - restore()                                            // Restore bound user to current session user
 *
 *   + Read
 *   - getUser([$id])                                       // Get current user or specified user
 *   - getUserList($ids)                                    // List of users by ID list
 *   - getIds($condition[, $limit[, $offset[, $order]]])    // ID list subject to $condition
 *   - getCount([$condition])                               // User count to $condition
 *
 *   + Add
 *   - addUser($data)               // Add a new user with account and profile
 *   + Update
 *   - updateUser($data[, $id])     // Update a user for account and profile
 *   + Delete
 *   - deleteUser($id)              // Delete a user
 *   + Activate
 *   - activateUser($id)            // Activate a user
 *   - deactivateUser($id)          // Deactivate a user
 *
 * + User account/profile field operations
 *   + Read
 *   - get($key[, $id])             // Get user field(s)
 *   - getList($key, $ids)          // User field(s) of user list
 *
 *   + Update
 *   - set($key, $value[, $id])         // Update field of user
 *   - increment($key, $value[, $id])   // Increase value of field
 *   - setPassword($value[, $id])       // Update password
 *
 * + Utility
 *   + Collective URL
 *   - getUrl($type[, $id])                                         // URLs with type: profile, login, logout, register, auth (authentication)
 *   + Authentication
 *   - authenticate($identity, $credential[, $identityField])       // Authenticate a user
 *
 * + External APIs
 * + Avatar
 *   - avatar([$id])                                                                // Get avatar handler
 *   - avatar([$id])->setSource($source)                                            // Set avatar source: upload, gravatar, local, empty for auto
 *   - avatar([$id])->get([$size[, $attributes[, $source]]])                        // Get avatar of a user
 *   - avatar([$id])->getList($ids[, $size[, $attributes[, $source]]])              // Get avatars of a list of users
 *   - avatar([$id])->set($value[, $source])                                        // Set avatar for a user
 *   - avatar([$id])->delete()                                                      // Delete user avatar
 *
 * + Message
 *   - message([$id])                                                               // Get message handler
 *   - message([$id])->send($message, $from)                                        // Send message to a user
 *   - message([$id])->notify($message, $subject[, $tag])                           // Send notification to a user
 *   - message([$id])->getCount()                                                   // Get message total count of current user
 *   - message([$id])->getAlert()                                                   // Get message alert (new) count of current user
 *
 * + Timeline/Activity
 *   - timeline([$id])                                                              // Get timeline handler
 *   - timeline([$id])->get($limit[, $offset[, $condition]])                        // Get timeline list
 *   - timeline([$id])->getCount([$condition]])                                     // Get timeline count subject to condition
 *   - timeline([$id])->add($message, $module[, $tag[, $time]])                     // Add activity to user timeline
 *   - timeline([$id])->getActivity($name, $limit[, $offset[, $condition]])         // Get activity list of a user
 *   - timeline([$id])->delete([$condition])                                        // Delete timeline items subjecto to condition
 *
 * + Relation
 *   - relation([$id])                                                              // Get relation handler
 *   - relation([$id])->get($relation, $limit[, $offset[, $condition[, $order]]])   // Get IDs with relationship: friend, follower, following
 *   - relation([$id])->getCount($relation[, $condition]])                          // Get count with relationship: friend, follower, following
 *   - relation([$id])->hasRelation($uid, $relation)                                // Check if $id has relation with $uid: friend, follower, following
 *   - relation([$id])->add($uid, $relation)                                        // Add $uid as a relation: friend, follower, following
 *   - relation([$id])->delete([$uid[, $relation]])                                 // Delete $uid as relation: friend, follower, following
 */
```

### Databases table
![Database design](https://raw.github.com/liuchuangww/image/master/user/userSystemDatabases.png)