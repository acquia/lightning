# Git Configuration

This directory contains [git hooks](https://git-scm.com/book/en/v2/Customizing-Git-Git-Hooks) used during Lightning developement. If you choose, you can use these git hooks by symlinking your local .git repo's hooks directory here. E.g., from within your .git dirctory (after deleting the hooks directory that `git init` creates):

    $ ln -s ../PATH_TO_LIGHTNING/git-hooks hooks
