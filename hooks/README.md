## What are Cloud Hooks?

Cloud Hooks is a feature of Acquia Cloud, the Drupal cloud hosting platform. For more information, see https://www.acquia.com/products-services/acquia-dev-cloud.

The Acquia Cloud Workflow page automates the most common tasks involved in developing a Drupal site: deploying code from a version control system, and migrating code, databases, and files across your Development, Staging, and Production environments. Cloud Hooks allow you to automate other tasks as part of these migrations.

A Cloud Hook is simply a script in your code repository that Acquia Cloud executes on your behalf when a triggering action occurs. Examples of tasks that you can automate with Cloud Hooks include:

* Perform Drupal database updates each time new code is deployed.
* "Scrub" your Production database when it is copied to Dev or Staging by removing customer emails or disabling production-only modules.
* Run your test suite or a site performance test each time new code is deployed.

## Installing Cloud Hooks

Cloud hook scripts live in your Acquia Cloud code repository. In each branch of your repo, there is a directory named docroot that contains your site's source code. Cloud hooks live in the directory hooks NEXT TO docroot (not inside of docroot).

To install the correct directory structure and sample hook scripts, simply copy this repo into your Acquia Cloud repo.

If you are using Git:

    cd /my/repo
    curl -L -o hooks.tar.gz https://github.com/acquia/cloud-hooks/tarball/master
    tar xzf hooks.tar.gz
    mv acquia-cloud-hooks-* hooks
    git add hooks
    git commit -m 'Import Cloud hooks directory and sample scripts.'
    git push

If you are using SVN:

    cd /my/repo
    curl -L -o hooks.tar.gz https://github.com/acquia/cloud-hooks/tarball/master
    tar xzf hooks.tar.gz
    mv acquia-cloud-hooks-* hooks
    svn add hooks
    svn commit -m 'Import Cloud hooks directory and sample scripts.'

## Quick Start

To get an idea of the power of Cloud Hooks, let's run the "Hello, Cloud!" script when new code is deployed in to your Dev environment.

1. Install the hello-world.sh script to run on code deployments to Dev. *This example assumes your Dev environment is running the 'master' branch*.

        cd /my/repo
        git checkout master
        cp hooks/samples/hello-world.sh hooks/dev/post-code-deploy
        git commit -a 'Run the hello-world script on post-code-deploy to Dev.'
        git push

2. Visit the Workflow page in the Acquia Cloud UI. In the Dev environment, select the 'master' branch (if your Dev environment is already running master, select any other tag and then select master again), then press Deploy.

3. Scroll down on the Workflow page. When the code deployment task is done, click its "Show" link to see the hook's output. It will look like this:

        Started
        Updating s1.dev to deploy master
        Deploying master on s1.dev
        [05:28:33] Starting hook: post-code-deploy
        Executing: /var/www/html/s1.dev/hooks/dev/post-code-deploy/hello-world.sh s1 dev master master s1@svn-3.bjaspan.hosting.acquia.com:s1.git git (as s1@srv-4)
        Hello, Cloud!
        [05:28:34] Finished hook: post-code-deploy

You can use the Code drop-down list to put your Dev environment back to whatever it was previously deploying.

## The Cloud Hooks directory

The hooks directory in your repo has a directory structure like this:

    /hooks / [env] / [hook] / [script]

* [env] is a directory whose name is an environment name: 'dev' for Development, 'test' for Staging, and 'prod' for Production, as well as 'common' for all environments.

* [hook] is a directory whose name is a Cloud Hook name: see below for supported hooks.

* [script] is a program or shell script within the [env]/[hook] directory.

Each time a hookable action occurs, Acquia Cloud runs scripts from the directory common/[hook] and [target-env]/[hook]. All scripts in the hook directory are run, in lexicographical (shell glob) order. If one of the hook scripts exits with non-zero status, the remaining hook scripts are skipped, and the task is marked "failed" on the Workflow page so you know to check it. All stdout and stderr output from all the hooks that ran are displayed in the task log on the Workflow page.

Note that hook scripts must have the Unix "executable" bit in order to run. If your script has the execute bit set when you first add it to Git or SVN, you're all set. Otherwise, to set the execute bit to a file already in your Git repo:

    chmod a+x ./my-hook.sh
    git add ./my-hook.sh
    git commit -m 'Add execute bit to my-hook.sh'
    git push

If you are using SVN:

    chmod a+x ./my-hook.sh
    svn propset svn:executable ON ./my-hook.sh
    svn commit -m 'Add execute bit to my-hook.sh'

## Sample scripts

The samples directory contains bare-bones example scripts for each of the supported hooks, plus a variety of useful user-contributed scripts. Each script starts with comments explaining what it is for and how it works.

Sample scripts currently include:

* post-code-deploy.tmpl: Template for post-code-deploy hook scripts.
* post-code-update.tmpl: Template for post-code-update hook scripts.
* post-db-copy.tmpl: Template for post-db-copy hook scripts.
* post-files-copy.tmpl: Template for post-files-copy hook scripts.
* update-db.sh: Run drush updatedb to perform database updates.
* db-scrub.sh: Scrub important information from a Drupal database.
* drupal-tests.sh: Run Drupal simpletests.
* rollback.sh: Run designated simpletest testing against a branch/tag and rollback on failure.
* newrelic.sh: Example of Acquia Hosting Cloud Hook to notify New Relic API of code version deployments.


## Supported hooks

This section defines the currently supported Cloud Hooks and the command-line arguments they receive.

### post-code-deploy

The post-code-deploy hook is run whenever you use the Workflow page to deploy new code to an environment, either via drag-drop or by selecting an existing branch or tag from the Code drop-down list. (The post-code-update hook runs after every code commit.)

Usage: post-code-deploy site target-env source-branch deployed-tag repo-url repo-type

* site: The site name. This is the same as the Acquia Cloud username for the site.
* target-env: The environment to which code was just deployed.
* source-branch: The code branch or tag being deployed. See below.
* deployed-tag: The code branch or tag being deployed. See below.
* repo-url: The URL of your code repository.
* repo-type: The version control system your site is using; "git" or "svn".

The meaning of source-branch and deployed-tag depends on whether you use drag-drop to move code from one environment to another or whether you select a new branch or tag for an environment from the Code drop-down list:

* With drag-drop, the "source branch" is the branch or tag that the environment you dragged from is set to, and the "deployed tag" is the  tag just deployed in the target environment. If source-branch is a branch (does not start with "tags/"), deployed-tag will be a newly created tag pointing at the tip of source-branch. If source-branch is a tag, deployed-tag will be the same tag.

* With the Code drop-down list, source-branch and deployed-tag will both be the name of branch or tag selected from the drop-down list.

Example: If the Dev environment is deploying the master branch and you drag Dev code to Stage, the code-deploy arguments will be like:

    post-code-deploy mysite test master tags/2011-11-05 mysite@svn-3.devcloud.hosting.acquia.com:mysite.git git

### post-code-update

The post-code-update hook runs in response to code commits. When you push commits to a Git branch, the post-code-update hooks runs for each environment that is currently running that branch.

The arguments for post-code-update are the same as for post-code-deploy, with the source-branch and deployed-tag arguments both set to the name of the environment receiving the new code.

post-code-update only runs if your site is using a Git repository. It does not support SVN.

### post-db-copy

The post-db-copy hook is run whenever you use the Workflow page to copy a database from one environment to another.

Usage: post-db-copy site target-env db-name source-env

* site: The site name. This is the same as the Acquia Cloud username for the site.
* target-env: The environment to which the database was copied.
* db-name: The name of the database that was copied. See below.
* source-env: The environment from which the database was copied.

db-name is not the actual MySQL database name but rather the common name for the database in all environments. Use the drush ah-sql-cli  to connect to the actual MySQL database, or use the drush ah-sql-connect command to convert the site name and target environment into the specific MySQL database name and credentials. (The drush sql-cli and sql-connect commands work too, but only if your Drupal installation is set up correctly.)

Example: To "scrub" your production database by removing all user accounts every time it is copied into your Stage environment, put this script into /hooks/test/post-db-copy/delete-users.sh:

    #!/bin/bash
    site=$1
    env=$2
    db=$3
    echo "DELETE FROM users" | drush @$site.$env ah-sql-cli --db=$db

### post-files-copy

The post-files-copy hook is run whenever you use the Workflow page to copy the user-uploaded files directory from one environment to another.

Usage: post-files-copy site target-env source-env

* site: The site name. This is the same as the Acquia Cloud username for the site.
* target-env: The environment to which files were copied.
* source-env: The environment from which the files were copied.

Example: When you use the Workflow page to drag files from Prod to Dev, the files-copy hook will be run like:

    post-files-copy mysite prod dev
