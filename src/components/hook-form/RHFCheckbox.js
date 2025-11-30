import { forwardRef } from 'react';
import PropTypes from 'prop-types';
import { FormControlLabel, Checkbox } from '@mui/material';
import { useController } from 'react-hook-form';

const RHFCheckbox = forwardRef(({ name, label, ...other }, ref) => {
  const { field } = useController({ name });

  return (
    <FormControlLabel
      control={
        <Checkbox
          {...field}
          checked={field.value}
          {...other}
        />
      }
      label={label}
    />
  );
});

RHFCheckbox.propTypes = {
  label: PropTypes.string,
  name: PropTypes.string,
};

export default RHFCheckbox; 