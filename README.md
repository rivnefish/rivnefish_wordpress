rivnefish
=========

WordPress Development
---------------------
Class Reference http://codex.wordpress.org/Class_Reference
Function Reference http://codex.wordpress.org/Function_Reference
Database query interface http://codex.wordpress.org/Class_Reference/wpdb

Development process
-------------------

1. Оберіть фічу(чи дефект) над якою ви будете працювати.

2. Створіть нову бренчу в якій ви будете робити ваші зміни

```bash
git checkout -b branch_name # це зробить нову бренчу із потчної і переключиться на неї
git push origin branch_name # надішле цю бренчу на сервер, краще зробити це одразу
git branch -u origin/branch_name # прив"яже вашу локальну бренчу до бренчі на сервері
```

Ви можете створити нову бренчу і на github, а потім локально переключитись на неї:

```bash
git pull # отримає всі зміни з сервера
git checkout branch_name # переключить на бренчу
```

Давайте назву бренчі так, щоб було зрозуміло для чого вона:
*ui_responsive_layout*, *fish_map_plugin_refactoring*, ...

3. Всі зміни мають йти в вашу бренчу. Робіть регулярні коміти в локальну бречу із зрозумілим описом змін. 

Регулярно "пушайте" ваші зміни на сервер:

```git push origin branch_name```

4. Створіть pull request на **master** для ревью змін, для цього на github:

- виберіть вашу бренчу з випадаючого списку
- клікніть *pull request*
- опишіть зроблені зміни, вставте лінк на опис фічі чи дефекту, і т.д.

Пишіть коментарі по коду в вкладці Files Changed, а не на коміт.

Якщо є багато запитань/коментарів не прив"язаних до коду, то напишіть їх одним повідомленням.

*Pull request* можна робити навіть коли фіча ще не готова.

5. Пофіксайте всі коментарі і після того як 2-є учасників схвалять зміни, змержіть вашу бренчу в master.
Найпростіше це зробити прямо в *pull request*-і просто натиснувши Merge.

Useful GIT commands
-------------------
```bash
# Set new user
git config --local user.name "h-yaroslav"
git config --local user.email "h.yaroslav@gmail.com"

git config user.name # See current user
git config --list    # See all variables

```