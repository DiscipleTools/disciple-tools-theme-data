# Cookbook for Disciple Tools Geonames file

### 1. Get Source File from Saturation Grid Project
[Saturation Grid Project > data_source > saturation-grid-geonames.tsv](https://github.com/DiscipleTools/saturation-grid-project/tree/master/data_source)

### 2. Upload to SQL 
### 3. Add DT specific columns
```apacheconfig
alt_name
alt_population
is_custom_location
alt_name_changed
```
### 4. Duplicate name column to alt_name column